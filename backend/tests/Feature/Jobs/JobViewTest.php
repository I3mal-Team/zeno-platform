<?php

declare(strict_types=1);

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('counts a candidate opening a listing', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->actingAs(makeUser('candidate'), 'sanctum')->getJson("/api/v1/jobs/{$job->slug}")->assertOk();

    expect($job->fresh()->views_count)->toBe(1)
        ->and(DB::table('job_views')->where('job_id', $job->id)->count())->toBe(1);
});

it('counts one view per viewer per day however often they refresh', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    foreach (range(1, 4) as $ignored) {
        test()->actingAs($candidate, 'sanctum')->getJson("/api/v1/jobs/{$job->slug}")->assertOk();
    }

    expect($job->fresh()->views_count)->toBe(1);
});

it('counts two different viewers separately', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->actingAs(makeUser('candidate'), 'sanctum')->getJson("/api/v1/jobs/{$job->slug}");
    test()->actingAs(makeUser('candidate'), 'sanctum')->getJson("/api/v1/jobs/{$job->slug}");

    expect($job->fresh()->views_count)->toBe(2);
});

it('does not count the employer looking at their own listing', function () {
    // Otherwise the number an employer checks would be partly themselves.
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->actingAs($employer, 'sanctum')->getJson("/api/v1/jobs/{$job->slug}")->assertOk();

    expect($job->fresh()->views_count)->toBe(0);
});

it('counts a guest opening the listing on the public site', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);

    test()->get("/jobs/{$job->slug}")->assertOk();

    expect($job->fresh()->views_count)->toBe(1);
});

it('adds every listing view into the employer overview total', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $first = activeJobFor($org, $employer);
    $second = activeJobFor($org, $employer, ['title' => 'كاشير']);

    test()->actingAs(makeUser('candidate'), 'sanctum')->getJson("/api/v1/jobs/{$first->slug}");
    test()->actingAs(makeUser('candidate'), 'sanctum')->getJson("/api/v1/jobs/{$second->slug}");

    expect((int) Job::query()->where('organization_id', $org->id)->sum('views_count'))->toBe(2);

    test()->actingAs($employer)->get('/employer')->assertOk()->assertSee('إجمالي المشاهدات', false);
});
