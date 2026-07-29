<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** Applies as a fresh candidate and returns the new application. */
function applyForVacancy(Job $job): Application
{
    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertCreated();

    return Application::query()->latest('id')->firstOrFail();
}

it('stops the listing taking applicants once its last vacancy is filled', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);
    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept")
        ->assertOk();

    expect($job->fresh()->status)->toBe(JobStatus::Filled);
});

it('keeps the listing active while vacancies remain', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 2]);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept")
        ->assertOk();

    expect($job->fresh()->status)->toBe(JobStatus::Active);
});

it('records who filled the listing as the system, not the employer', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    $entry = DB::table('job_status_history')->where('job_id', $job->id)->latest('id')->first();

    expect($entry->to_status)->toBe('filled')
        ->and($entry->actor_type)->toBe('system')
        ->and($entry->reason)->toBe('vacancies_filled');
});

it('turns a new applicant away from a listing that has just filled', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'JOB_NOT_ACTIVE');
});

it('drops the filled listing out of the public feed', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1, 'title' => 'باريستا مكتمل']);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    test()->getJson('/api/v1/jobs')->assertOk()->assertJsonMissing(['title' => 'باريستا مكتمل']);
});

it('still opens the filled listing for someone who already applied', function () {
    // Their application is live; they must be able to read what they applied to.
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    test()->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.is_open_for_applications', false);
});

it('lets the employer put a filled listing back to active when a hire falls through', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);

    $application = applyForVacancy($job);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept");

    expect($job->fresh()->status)->toBe(JobStatus::Filled);

    test()->actingAs($employer)->post("/employer/jobs/{$job->uuid}/resume")->assertRedirect();

    expect($job->fresh()->status)->toBe(JobStatus::Active);
});

it('tells the employer the listing stopped taking applicants', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);
    $application = applyForVacancy($job);

    test()->actingAs($employer)
        ->post("/employer/applications/{$application->id}/accept")
        ->assertSessionHas('status', fn (string $message) => str_contains($message, 'اكتمل عدد الشواغر'));
});
