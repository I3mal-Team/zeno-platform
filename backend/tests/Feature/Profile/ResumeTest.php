<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\CandidateProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('uploads a resume over the API', function () {
    Storage::fake('public');
    $user = makeUser('candidate');

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/profile/candidate/resume', [
            'resume' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ])
        ->assertOk()
        ->assertJsonPath('data.resume_url', fn ($url) => is_string($url) && $url !== '');
});

it('rejects a resume that is not a document', function () {
    $user = makeUser('candidate');

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/profile/candidate/resume', [
            'resume' => UploadedFile::fake()->image('me.jpg'),
        ])
        ->assertStatus(422);
});

it('uploads a resume from the web profile page', function () {
    Storage::fake('public');
    $user = makeUser('candidate');

    test()->actingAs($user)
        ->post(route('profile.resume'), [
            'resume' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ])
        ->assertRedirect(route('profile.edit'));

    $profile = CandidateProfile::query()->where('user_id', $user->id)->firstOrFail();

    expect($profile->getFirstMediaUrl(CandidateProfile::RESUME_COLLECTION))->not->toBe('');
});

it('shows the applicant resume to the employer', function () {
    Storage::fake('public');
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    $candidate->candidateProfile
        ->addMedia(UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'))
        ->toMediaCollection(CandidateProfile::RESUME_COLLECTION);

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();

    test()->actingAs($employer, 'sanctum')
        ->getJson("/api/v1/employer/applications/{$application->id}")
        ->assertOk()
        ->assertJsonPath('data.profile.resume_url', fn ($url) => is_string($url) && $url !== '');
});
