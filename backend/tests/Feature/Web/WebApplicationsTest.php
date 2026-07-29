<?php

declare(strict_types=1);

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** @return array{0: User, 1: Job} */
function candidateWithApplication(): array
{
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");

    return [$candidate, $job];
}

it('shows the candidate their applications on the site', function () {
    [$candidate, $job] = candidateWithApplication();

    test()->actingAs($candidate)->get('/applications')
        ->assertOk()
        ->assertSee($job->title);
});

it('lets the candidate withdraw an application from the site', function () {
    [$candidate] = candidateWithApplication();
    $reference = (string) Application::query()->value('reference_number');

    test()->actingAs($candidate)
        ->post("/applications/{$reference}/withdraw")
        ->assertRedirect();

    // Read the raw column so the enum cast doesn't turn it back into a case.
    expect(DB::table('applications')->value('status'))->toBe('withdrawn');
});

it('keeps employers out of the candidate applications route', function () {
    [$employer] = makeEmployerWithOrg(verified: true);

    test()->actingAs($employer)->get('/applications')->assertRedirect();
});
