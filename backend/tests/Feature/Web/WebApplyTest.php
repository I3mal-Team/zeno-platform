<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use App\Models\Application;
use App\Models\City;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function webActiveJob(Organization $org, User $creator): Job
{
    return Job::query()->create([
        'uuid' => (string) Str::uuid(),
        'organization_id' => $org->id,
        'created_by_user_id' => $creator->id,
        'title' => 'كاشير',
        'slug' => 'job-'.Str::lower(Str::random(8)),
        'category_id' => DB::table('categories')->value('id'),
        'work_type_id' => refId('work_types', 'full_time'),
        'salary_unit_id' => refId('salary_units', 'monthly'),
        'gender_requirement_id' => refId('gender_requirements', 'all'),
        'nationality_requirement_id' => refId('nationality_requirements', 'all'),
        'salary_amount' => 4200,
        'vacancies_count' => 1,
        'city_id' => City::query()->value('id'),
        'contact_channel' => 'app',
        'status' => JobStatus::Active->value,
        'published_at' => now(),
    ]);
}

it('lets a signed-in candidate apply from the website', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = webActiveJob($org, $employer);
    $candidate = makeUser('candidate');

    test()->actingAs($candidate)
        ->post("/jobs/{$job->slug}/apply")
        ->assertRedirect(route('site.jobs.show', $job->slug));

    expect(Application::query()->where('candidate_id', $candidate->id)->exists())->toBeTrue();
});

it('shows the employer their applicants and lets them accept', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = webActiveJob($org, $employer);
    $candidate = makeUser('candidate');
    test()->actingAs($candidate)->post("/jobs/{$job->slug}/apply");
    $application = Application::query()->firstOrFail();

    test()->actingAs($employer)->get('/employer/applicants')->assertOk()->assertSee('المتقدّمون', false);
    test()->actingAs($employer)->get("/employer/applications/{$application->id}")->assertOk();

    test()->actingAs($employer)->post("/employer/applications/{$application->id}/accept")->assertRedirect();
    expect($application->fresh()->status->value)->toBe('accepted');
});
