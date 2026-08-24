<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Notifications\JobChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** Creates a live application by a fresh candidate for the given job. */
function applyTo(Job $job): User
{
    $candidate = makeUser('candidate');

    Application::query()->create([
        'reference_number' => (string) fake()->unique()->numerify('APP#######'),
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'organization_id' => $job->organization_id,
        'status' => 'submitted',
        'contact_channel' => 'app',
        'profile_access_token' => (string) Str::ulid(),
        'profile_access_expires_at' => now()->addDays(30),
    ]);

    return $candidate;
}

function publishJob(User $user): string
{
    return test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->json('data.id');
}

it('notifies applicants when a material term changes', function () {
    Notification::fake();
    [$user] = makeEmployerWithOrg(verified: true);
    $uuid = publishJob($user);
    $job = Job::query()->where('uuid', $uuid)->firstOrFail();
    $candidate = applyTo($job);

    test()->actingAs($user, 'sanctum')
        ->putJson("/api/v1/employer/jobs/{$uuid}", jobPayload(['salary_amount' => 7000]))
        ->assertOk();

    expect($job->revisions()->count())->toBe(1);
    Notification::assertSentTo($candidate, JobChangedNotification::class);
});

it('does not notify when only a non-material field changes', function () {
    Notification::fake();
    [$user] = makeEmployerWithOrg(verified: true);
    $uuid = publishJob($user);
    $job = Job::query()->where('uuid', $uuid)->firstOrFail();
    applyTo($job);

    test()->actingAs($user, 'sanctum')
        ->putJson("/api/v1/employer/jobs/{$uuid}", jobPayload(['description' => 'وصف محدّث تماماً']))
        ->assertOk();

    expect($job->revisions()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('records no revision for a material change when there are no applicants', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    $uuid = publishJob($user);

    test()->actingAs($user, 'sanctum')
        ->putJson("/api/v1/employer/jobs/{$uuid}", jobPayload(['salary_amount' => 9000]))
        ->assertOk();

    expect(Job::query()->where('uuid', $uuid)->firstOrFail()->revisions()->count())->toBe(0);
});

it('pauses and resumes a listing', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    $uuid = publishJob($user);

    test()->actingAs($user, 'sanctum')
        ->postJson("/api/v1/employer/jobs/{$uuid}/pause")
        ->assertOk()
        ->assertJsonPath('data.status', 'paused');

    test()->actingAs($user, 'sanctum')
        ->postJson("/api/v1/employer/jobs/{$uuid}/resume")
        ->assertOk()
        ->assertJsonPath('data.status', 'active');
});

it('rejects an illegal status transition', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    $uuid = publishJob($user);
    test()->actingAs($user, 'sanctum')->postJson("/api/v1/employer/jobs/{$uuid}/close");

    test()->actingAs($user, 'sanctum')
        ->postJson("/api/v1/employer/jobs/{$uuid}/pause")
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'JOB_INVALID_TRANSITION');
});
