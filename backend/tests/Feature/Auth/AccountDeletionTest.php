<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\Conversation;
use App\Models\DeviceToken;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function deleteAccountAs(User $user): TestResponse
{
    return test()->actingAs($user, 'sanctum')->deleteJson('/api/v1/auth/account');
}

it('closes the account and reports it', function () {
    $user = makeUser('candidate');

    deleteAccountAs($user)
        ->assertOk()
        ->assertJsonPath('success', true);

    $user->refresh();

    expect($user->trashed())->toBeTrue()
        ->and($user->status)->toBe('deleted');
});

it('revokes every token the account had', function () {
    $user = makeUser('candidate');
    $user->createToken('phone')->plainTextToken;
    $user->createToken('tablet')->plainTextToken;

    deleteAccountAs($user)->assertOk();

    expect($user->tokens()->count())->toBe(0);
});

it('erases the profile holding the personal data', function () {
    $user = makeUser('candidate');

    expect(CandidateProfile::query()->where('user_id', $user->id)->exists())->toBeTrue();

    deleteAccountAs($user)->assertOk();

    expect(CandidateProfile::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('takes the applications and everything hanging off them', function () {
    // Nothing in this chain soft deletes, so removing the applications is what
    // clears the conversations, their messages and the read receipts. If that
    // ever changes, the sheet's promise to delete the chats becomes a lie.
    [$employer, $organization] = makeEmployerWithOrg();
    $job = activeJobFor($organization, $employer);
    $candidate = makeUser('candidate');

    $application = Application::query()->create([
        'reference_number' => 'REF12345678',
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'organization_id' => $organization->id,
        'contact_channel' => 'app',
        'profile_access_token' => Str::lower(Str::random(26)),
        'profile_access_expires_at' => now()->addDays(30),
    ]);

    $conversation = Conversation::query()->create([
        'uuid' => (string) Str::uuid(),
        'application_id' => $application->id,
        'candidate_id' => $candidate->id,
        'organization_id' => $organization->id,
    ]);

    Message::query()->create([
        'uuid' => (string) Str::uuid(),
        'conversation_id' => $conversation->id,
        'sender_id' => $candidate->id,
        'type' => 'text',
        'body' => 'السلام عليكم، متى المقابلة؟',
        'client_uuid' => (string) Str::uuid(),
    ]);

    deleteAccountAs($candidate)->assertOk();

    expect(Application::query()->whereKey($application->id)->exists())->toBeFalse()
        ->and(Conversation::query()->whereKey($conversation->id)->exists())->toBeFalse()
        ->and(Message::query()->where('sender_id', $candidate->id)->exists())->toBeFalse();
});

it('takes the uploaded CV and photo off the disk too', function () {
    // The media library only detaches files from a model's deleting event, so a
    // query-builder mass delete drops the row and strands the CV in storage —
    // which is exactly what the app and the web page promise it will not do.
    Storage::fake('public');

    $candidate = makeUser('candidate');
    $profile = $candidate->candidateProfile;

    $profile->addMediaFromString('صورة')
        ->usingFileName('avatar.jpg')
        ->toMediaCollection(CandidateProfile::AVATAR_COLLECTION);

    $profile->addMediaFromString('سيرة ذاتية')
        ->usingFileName('cv.pdf')
        ->toMediaCollection(CandidateProfile::RESUME_COLLECTION);

    expect(Media::query()->count())->toBe(2);

    deleteAccountAs($candidate)->assertOk();

    expect(Media::query()->count())->toBe(0);
});

it('stops push by dropping every registered device', function () {
    $user = makeUser('candidate');

    DeviceToken::query()->create([
        'user_id' => $user->id,
        'token' => 'fcm-token-1',
        'platform' => 'android',
        'last_used_at' => now(),
    ]);

    deleteAccountAs($user)->assertOk();

    expect(DeviceToken::query()->where('user_id', $user->id)->count())->toBe(0);
});

it('frees the phone number so the same person can sign up again', function () {
    // The unique index on phone_e164 is partial on deleted_at IS NULL. Without
    // that, closing an account would lock its owner out of the platform for
    // good — which is the opposite of what a deletion feature is for.
    $user = makeUser('candidate', '+966512345678');

    deleteAccountAs($user)->assertOk();

    $returning = makeUser('candidate', '+966512345678');

    expect($returning->id)->not->toBe($user->id)
        ->and(User::query()->where('phone_e164', '+966512345678')->count())->toBe(1);
});

it('closes an employer account that still owns listings', function () {
    // jobs.created_by_user_id restricts on delete, so a hard delete here would
    // fail outright. This is the case that forces the soft-delete design.
    [$employer, $organization] = makeEmployerWithOrg();
    activeJobFor($organization, $employer);

    deleteAccountAs($employer)->assertOk();

    expect($employer->refresh()->trashed())->toBeTrue();
});

it('locks the closed account out of signing in again on the old session', function () {
    $user = makeUser('candidate');
    $token = $user->createToken('phone')->plainTextToken;

    deleteAccountAs($user)->assertOk();

    app('auth')->forgetGuards();

    test()->withToken($token)->getJson('/api/v1/auth/me')->assertStatus(401);
});

it('rejects an unauthenticated deletion', function () {
    test()->deleteJson('/api/v1/auth/account')
        ->assertStatus(401)
        ->assertJsonPath('error.code', 'SESSION_EXPIRED');
});
