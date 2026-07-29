<?php

declare(strict_types=1);

use App\Enums\VerificationRequestStatus;
use App\Enums\VerificationStatus;
use App\Models\VerificationRequest;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('public');
});

/**
 * A real PDF on disk. The media library sniffs the file's own bytes rather
 * than trusting the declared type, so a zero-byte fake is rejected as empty.
 */
function fakePdf(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'zeno').'.pdf';
    file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");

    return new UploadedFile($path, 'cr.pdf', 'application/pdf', null, true);
}

it('lets an unverified employer file a verification request', function () {
    [$employer, $org] = makeEmployerWithOrg();

    test()->actingAs($employer)
        ->post('/employer/verification', ['documents' => [fakePdf()]])
        ->assertRedirect(route('employer.verification.show'));

    $request = VerificationRequest::query()->firstOrFail();

    expect($request->organization_id)->toBe($org->id)
        ->and($request->status)->toBe(VerificationRequestStatus::Pending)
        ->and($request->getMedia(VerificationRequest::DOCUMENTS_COLLECTION))->toHaveCount(1)
        // Filing moves the badge to "under review" rather than leaving it blank.
        ->and($org->fresh()->verification_status)->toBe(VerificationStatus::Pending);
});

it('refuses a second request while one is still pending', function () {
    [$employer] = makeEmployerWithOrg();

    test()->actingAs($employer)->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('id.jpg')]]);

    test()->actingAs($employer)
        ->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('id.jpg')]])
        ->assertSessionHasErrors('form');

    expect(VerificationRequest::query()->count())->toBe(1);
});

it('rejects a document that is not an image or a pdf', function () {
    [$employer] = makeEmployerWithOrg();

    test()->actingAs($employer)
        ->post('/employer/verification', ['documents' => [UploadedFile::fake()->create('script.exe', 10)]])
        ->assertSessionHasErrors('documents.0');

    expect(VerificationRequest::query()->count())->toBe(0);
});

it('verifies the organization when an admin approves', function () {
    [$employer, $org] = makeEmployerWithOrg();

    test()->actingAs($employer)->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('id.jpg')]]);

    app(VerificationService::class)->decide(
        VerificationRequest::query()->firstOrFail(),
        VerificationRequestStatus::Approved,
        null,
    );

    expect($org->fresh()->verification_status)->toBe(VerificationStatus::Verified)
        ->and($org->fresh()->verified_at)->not->toBeNull();
});

it('returns the organization to unverified when an admin rejects, and lets them re-apply', function () {
    [$employer, $org] = makeEmployerWithOrg();

    test()->actingAs($employer)->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('id.jpg')]]);

    app(VerificationService::class)->decide(
        VerificationRequest::query()->firstOrFail(),
        VerificationRequestStatus::Rejected,
        null,
        'السجل التجاري منتهي',
    );

    expect($org->fresh()->verification_status)->toBe(VerificationStatus::Unverified);

    test()->actingAs($employer)->get('/employer/verification')->assertOk()->assertSee('السجل التجاري منتهي', false);

    test()->actingAs($employer)
        ->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('new.jpg')]])
        ->assertRedirect();

    expect(VerificationRequest::query()->count())->toBe(2);
});

it('refuses a request from an organization that is already verified', function () {
    [$employer] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer)
        ->post('/employer/verification', ['documents' => [UploadedFile::fake()->image('id.jpg')]])
        ->assertSessionHasErrors('form');
});

it('keeps candidates out of the verification page', function () {
    test()->actingAs(makeUser('candidate'))->get('/employer/verification')->assertRedirect(route('site.home'));
});
