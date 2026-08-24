<?php

declare(strict_types=1);

use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function applicationForEmployer(): array
{
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");

    return [$employer, Application::query()->firstOrFail()];
}

it('shortlists then un-shortlists an applicant over the API', function () {
    [$employer, $application] = applicationForEmployer();

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/shortlist")
        ->assertOk()
        ->assertJsonPath('data.shortlisted', true);

    expect($application->fresh()->shortlisted)->toBeTrue();

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/shortlist")
        ->assertOk()
        ->assertJsonPath('data.shortlisted', false);
});

it('saves and shows a private note over the API', function () {
    [$employer, $application] = applicationForEmployer();

    test()->actingAs($employer, 'sanctum')
        ->putJson("/api/v1/employer/applications/{$application->id}/note", ['note' => 'مرشّح قوي'])
        ->assertOk()
        ->assertJsonPath('data.note', 'مرشّح قوي');

    test()->actingAs($employer, 'sanctum')
        ->getJson("/api/v1/employer/applications/{$application->id}")
        ->assertOk()
        ->assertJsonPath('data.note', 'مرشّح قوي')
        ->assertJsonPath('data.shortlisted', false);
});

it('clears the note when given an empty value', function () {
    [$employer, $application] = applicationForEmployer();
    test()->actingAs($employer, 'sanctum')->putJson("/api/v1/employer/applications/{$application->id}/note", ['note' => 'مبدئي']);

    test()->actingAs($employer, 'sanctum')
        ->putJson("/api/v1/employer/applications/{$application->id}/note", ['note' => ''])
        ->assertOk();

    expect($application->fresh()->employer_note)->toBeNull();
});

it('does not let another organization shortlist the applicant', function () {
    [, $application] = applicationForEmployer();
    [$other] = makeEmployerWithOrg(verified: true);

    test()->actingAs($other, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/shortlist")
        ->assertNotFound();
});

it('shortlists an applicant from the web', function () {
    [$employer, $application] = applicationForEmployer();

    test()->actingAs($employer)
        ->from(route('employer.applicants.show', $application->id))
        ->post(route('employer.applicants.shortlist', $application->id))
        ->assertRedirect(route('employer.applicants.show', $application->id));

    expect($application->fresh()->shortlisted)->toBeTrue();
});
