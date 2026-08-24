<?php

declare(strict_types=1);

use App\Models\Admin;
use App\Models\Job;
use App\Services\JobModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function makeAdmin(): Admin
{
    return Admin::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'مشرف',
        'email' => 'admin+'.Str::lower(Str::random(6)).'@zeno.test',
        'password' => bcrypt('secret'),
        'status' => 'active',
    ]);
}

it('approving an unverified listing publishes it and grants the org auto-publish', function () {
    [$user, $organization] = makeEmployerWithOrg(verified: false);
    $uuid = test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())->json('data.id');
    $job = Job::query()->where('uuid', $uuid)->firstOrFail();

    app(JobModerationService::class)->approve(makeAdmin()->id, $job);

    expect($job->fresh()->status->value)->toBe('active')
        ->and($job->fresh()->published_at)->not->toBeNull()
        ->and($organization->fresh()->auto_publish_jobs)->toBeTrue();
});

it('lets a trusted org publish its next listing without review', function () {
    [$user] = makeEmployerWithOrg(verified: false);
    subscribeEmployer($user); // a plan that allows more than one listing
    $first = test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())->json('data.id');
    app(JobModerationService::class)->approve(makeAdmin()->id, Job::query()->where('uuid', $first)->firstOrFail());

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['title' => 'إعلان ثانٍ']))
        ->assertCreated()
        ->assertJsonPath('data.status', 'active');
});

it('rejecting a listing moves it to rejected with a reason on record', function () {
    [$user] = makeEmployerWithOrg(verified: false);
    $uuid = test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())->json('data.id');
    $job = Job::query()->where('uuid', $uuid)->firstOrFail();

    app(JobModerationService::class)->reject(makeAdmin()->id, $job, 'محتوى مخالف');

    expect($job->fresh()->status->value)->toBe('rejected');
    expect(DB::table('moderation_actions')->where('action', 'job_rejected')->count())->toBe(1);
});
