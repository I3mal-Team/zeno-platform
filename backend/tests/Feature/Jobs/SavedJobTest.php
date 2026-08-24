<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('saves a job and lists it under saved jobs', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/save")
        ->assertOk();

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/saved-jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $job->slug)
        ->assertJsonPath('data.0.is_saved', true);
});

it('flags a saved job on the detail and the browse feed', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/save");

    test()->actingAs($candidate, 'sanctum')
        ->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.is_saved', true);

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/jobs')
        ->assertOk()
        ->assertJsonPath('data.0.is_saved', true);
});

it('does not flag the job as saved for another candidate', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/save");

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.is_saved', false);
});

it('unsaves a job', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/save");

    test()->actingAs($candidate, 'sanctum')
        ->deleteJson("/api/v1/jobs/{$job->slug}/save")
        ->assertOk();

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/saved-jobs')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('treats saving the same job twice as one bookmark', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/save")->assertOk();
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/save")->assertOk();

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/saved-jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('requires a sign-in to save a job', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->postJson("/api/v1/jobs/{$job->slug}/save")->assertUnauthorized();
});

it('saves and lists a job from the website', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate)
        ->from(route('site.jobs.show', $job->slug))
        ->post(route('site.jobs.save', $job->slug))
        ->assertRedirect(route('site.jobs.show', $job->slug));

    test()->actingAs($candidate)
        ->get(route('site.saved'))
        ->assertOk()
        ->assertSee($job->title, false);
});

it('removes a saved job from the website', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate)->post(route('site.jobs.save', $job->slug));

    test()->actingAs($candidate)
        ->from(route('site.saved'))
        ->delete(route('site.jobs.unsave', $job->slug))
        ->assertRedirect(route('site.saved'));

    expect($candidate->savedJobs()->count())->toBe(0);
});
