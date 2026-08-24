<?php

declare(strict_types=1);

use App\Data\Jobs\JobData;
use App\Repositories\JobRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function placeJob(int $jobId, float $lat, float $lng): void
{
    DB::statement(
        'UPDATE jobs SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
        [$lng, $lat, $jobId],
    );
}

it('gives every published listing a location from its city or district', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertCreated();

    expect(DB::table('jobs')->whereNotNull('location')->count())->toBe(1);
});

it('lists jobs near a point, nearest first, with the distance', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $near = activeJobFor($org, $employer, ['title' => 'قريبة']);
    $far = activeJobFor($org, $employer, ['title' => 'بعيدة']);

    // Riyadh centre, and a point ~50 km east.
    placeJob($near->id, 24.7136, 46.6753);
    placeJob($far->id, 24.7136, 47.2);

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->getJson('/api/v1/jobs/nearby?lat=24.7136&lng=46.6753&radius=100')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', $near->slug)
        ->assertJsonPath('data.0.distance_km', fn ($d) => is_numeric($d) && $d < 1)
        ->assertJsonPath('data.1.slug', $far->slug);
});

it('excludes jobs beyond the radius', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $near = activeJobFor($org, $employer);
    $far = activeJobFor($org, $employer);
    placeJob($near->id, 24.7136, 46.6753);
    placeJob($far->id, 24.7136, 47.2); // ~50 km away

    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->getJson('/api/v1/jobs/nearby?lat=24.7136&lng=46.6753&radius=10')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $near->slug);
});

it('falls back to the candidate city centre when no coordinates are given', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $cityId = (int) DB::table('cities')->whereNotNull('center_point')->value('id');

    $candidate = makeUser('candidate');
    $candidate->candidateProfile->update(['city_id' => $cityId]);

    $job = activeJobFor($org, $employer, ['city_id' => $cityId]);
    DB::statement(
        'UPDATE jobs SET location = (SELECT center_point FROM cities WHERE id = ?) WHERE id = ?',
        [$cityId, $job->id],
    );

    test()->actingAs($candidate, 'sanctum')
        ->getJson('/api/v1/jobs/nearby')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $job->slug);
});

it('returns nothing when there is no location to search from', function () {
    // A candidate with no city and no coordinates has no origin.
    test()->actingAs(makeUser('candidate'), 'sanctum')
        ->getJson('/api/v1/jobs/nearby')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('keeps an existing pin when an edit omits coordinates', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $job = activeJobFor($org, $employer);
    placeJob($job->id, 24.7136, 46.6753);

    // Re-run the location write with no coordinates (as an edit would).
    app(JobRepository::class)->update($job, new JobData(
        title: $job->title,
        description: null,
        categoryId: $job->category_id,
        workTypeId: $job->work_type_id,
        salaryUnitId: $job->salary_unit_id,
        genderRequirementId: $job->gender_requirement_id,
        nationalityRequirementId: $job->nationality_requirement_id,
        salaryAmount: (float) $job->salary_amount,
        salaryAmountMax: null,
        hoursPerWeek: null,
        shiftNote: null,
        vacanciesCount: $job->vacancies_count,
        cityId: $job->city_id,
        districtId: null,
        addressLine: null,
        latitude: null,
        longitude: null,
        contactChannel: $job->contact_channel,
        expiresAt: null,
    ));

    $lng = DB::selectOne('SELECT ST_X(location::geometry) AS lng FROM jobs WHERE id = ?', [$job->id])->lng;
    expect(round((float) $lng, 4))->toBe(46.6753);
});

it('renders the web nearby page from the candidate city', function () {
    [$employer, $org] = makeEmployerWithOrg(verified: true);
    $cityId = (int) DB::table('cities')->whereNotNull('center_point')->value('id');
    $candidate = makeUser('candidate');
    $candidate->candidateProfile->update(['city_id' => $cityId]);
    $job = activeJobFor($org, $employer, ['city_id' => $cityId, 'title' => 'وظيفة قريبة']);
    DB::statement('UPDATE jobs SET location = (SELECT center_point FROM cities WHERE id = ?) WHERE id = ?', [$cityId, $job->id]);

    test()->actingAs($candidate)
        ->get(route('site.jobs.nearby'))
        ->assertOk()
        ->assertSee('وظيفة قريبة', false);
});
