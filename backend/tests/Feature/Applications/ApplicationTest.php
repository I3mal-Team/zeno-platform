<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use App\Models\Application;
use App\Models\City;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\ApplicationDecisionNotification;
use App\Notifications\ApplicationSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function activeJobFor(Organization $org, User $creator, array $overrides = []): Job
{
    return Job::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'organization_id' => $org->id,
        'created_by_user_id' => $creator->id,
        'title' => 'باريستا',
        'slug' => 'job-'.Str::lower(Str::random(8)),
        'category_id' => DB::table('categories')->value('id'),
        'work_type_id' => refId('work_types', 'full_time'),
        'salary_unit_id' => refId('salary_units', 'monthly'),
        'gender_requirement_id' => refId('gender_requirements', 'all'),
        'nationality_requirement_id' => refId('nationality_requirements', 'all'),
        'salary_amount' => 4500,
        'vacancies_count' => 2,
        'city_id' => City::query()->value('id'),
        'contact_channel' => 'app',
        'status' => JobStatus::Active->value,
        'published_at' => now(),
    ], $overrides));
}

it('lets a candidate apply and notifies the employer', function () {
    Notification::fake();
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertCreated()
        ->assertJsonPath('data.status', 'submitted');

    expect(Application::query()->where('candidate_id', $candidate->id)->exists())->toBeTrue();
    Notification::assertSentTo($employer, ApplicationSubmittedNotification::class);
});

it('rejects a second live application to the same job', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply")->assertCreated();
    test()->actingAs($candidate, 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'APPLICATION_ALREADY_EXISTS');
});

it('refuses an application to a paused job', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['status' => JobStatus::Paused->value]);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->postJson("/api/v1/jobs/{$job->slug}/apply")
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'JOB_NOT_ACTIVE');
});

it('lists the candidate own applications and allows withdrawal', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    $ref = test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply")->json('data.reference');

    test()->actingAs($candidate, 'sanctum')->getJson('/api/v1/applications')->assertOk()->assertJsonCount(1, 'data');

    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/applications/{$ref}/withdraw")->assertOk();
    expect(DB::table('applications')->where('reference_number', $ref)->value('status'))->toBe('withdrawn');
});

it('lists every applicant across the employer listings', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $jobA = activeJobFor($org, $employer);
    $jobB = activeJobFor($org, $employer);
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$jobA->slug}/apply");
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$jobB->slug}/apply");

    test()->actingAs($employer, 'sanctum')
        ->getJson('/api/v1/employer/applications')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('moves an application to review when the employer opens it', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();

    test()->actingAs($employer, 'sanctum')->getJson("/api/v1/employer/applications/{$application->id}")->assertOk();

    expect($application->fresh()->status->value)->toBe('review');
});

it('accepts an applicant and notifies the candidate', function () {
    Notification::fake();
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept")
        ->assertOk()
        ->assertJsonPath('data.status', 'accepted')
        ->assertJsonPath('data.job_filled', false);

    Notification::assertSentTo($candidate, ApplicationDecisionNotification::class);
});

it('enforces the vacancy limit on acceptance (D-13)', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer, ['vacancies_count' => 1]);

    $first = makeUser('candidate');
    $second = makeUser('candidate');
    test()->actingAs($first, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    test()->actingAs($second, 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $apps = Application::query()->orderBy('id')->get();

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$apps[0]->id}/accept")
        ->assertOk()
        ->assertJsonPath('data.job_filled', true);

    test()->actingAs($employer, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$apps[1]->id}/accept")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'VACANCIES_EXHAUSTED');
});

it('does not expose one employer applicants to another', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    test()->actingAs(makeUser('candidate'), 'sanctum')->postJson("/api/v1/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();

    [$other] = makeEmployerWithOrg(verified: true);
    test()->actingAs($other, 'sanctum')
        ->postJson("/api/v1/employer/applications/{$application->id}/accept")
        ->assertNotFound();
});
