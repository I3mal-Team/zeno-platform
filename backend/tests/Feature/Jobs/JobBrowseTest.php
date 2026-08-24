<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use App\Models\City;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

/** Publishes a listing directly in the given status, bypassing the HTTP layer. */
function seedJob(array $overrides = []): Job
{
    [$user, $organization] = makeEmployerWithOrg(verified: true);

    return Job::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'organization_id' => $organization->id,
        'created_by_user_id' => $user->id,
        'title' => 'وظيفة',
        'slug' => 'job-'.Str::lower(Str::random(8)),
        'category_id' => DB::table('categories')->value('id'),
        'work_type_id' => refId('work_types', 'full_time'),
        'salary_unit_id' => refId('salary_units', 'monthly'),
        'gender_requirement_id' => refId('gender_requirements', 'all'),
        'nationality_requirement_id' => refId('nationality_requirements', 'all'),
        'salary_amount' => 4000,
        'vacancies_count' => 1,
        'city_id' => City::query()->value('id'),
        'contact_channel' => 'app',
        'status' => JobStatus::Active->value,
        'published_at' => now(),
    ], $overrides));
}

it('lists only active listings', function () {
    seedJob(['status' => JobStatus::Active->value]);
    seedJob(['status' => JobStatus::Paused->value]);
    seedJob(['status' => JobStatus::PendingReview->value, 'published_at' => null]);

    test()->getJson('/api/v1/jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters by salary floor', function () {
    seedJob(['salary_amount' => 3000]);
    seedJob(['salary_amount' => 6000]);

    test()->getJson('/api/v1/jobs?salary_min=5000')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('opens an active listing by slug', function () {
    $job = seedJob(['status' => JobStatus::Active->value]);

    test()->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.id', $job->uuid)
        ->assertJsonPath('data.is_open_for_applications', true);
});

it('still opens a paused listing by its direct link but marks it closed for applications', function () {
    $job = seedJob(['status' => JobStatus::Paused->value]);

    test()->getJson("/api/v1/jobs/{$job->slug}")
        ->assertOk()
        ->assertJsonPath('data.status', 'paused')
        ->assertJsonPath('data.is_open_for_applications', false);
});

it('hides a pending listing even by direct link', function () {
    $job = seedJob(['status' => JobStatus::PendingReview->value, 'published_at' => null]);

    test()->getJson("/api/v1/jobs/{$job->slug}")->assertStatus(404);
});
