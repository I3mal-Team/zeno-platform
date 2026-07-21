<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function seedJobFixture(): array
{
    $cityId = DB::table('cities')->insertGetId([
        'code' => 'test_city',
        'name' => json_encode(['ar' => 'مدينة'], JSON_UNESCAPED_UNICODE),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $userId = DB::table('users')->insertGetId([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000001', 'role' => 'employer',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $candidateId = DB::table('users')->insertGetId([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000002', 'role' => 'candidate',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $orgId = DB::table('organizations')->insertGetId([
        'uuid' => Str::uuid(), 'type' => 'individual', 'name' => 'منشأة', 'slug' => 'test-org',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $ref = fn (string $table) => DB::table($table)->value('id');

    $jobId = DB::table('jobs')->insertGetId([
        'uuid' => Str::uuid(),
        'organization_id' => $orgId,
        'created_by_user_id' => $userId,
        'title' => 'باريستا',
        'slug' => 'test-job',
        'category_id' => $ref('categories'),
        'work_type_id' => $ref('work_types'),
        'salary_unit_id' => $ref('salary_units'),
        'gender_requirement_id' => $ref('gender_requirements'),
        'nationality_requirement_id' => $ref('nationality_requirements'),
        'salary_amount' => 4500,
        'city_id' => $cityId,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return ['job' => $jobId, 'candidate' => $candidateId, 'org' => $orgId, 'user' => $userId];
}

function insertApplication(array $ids, string $status): void
{
    DB::table('applications')->insert([
        'reference_number' => (string) random_int(1000000, 9999999),
        'job_id' => $ids['job'],
        'candidate_id' => $ids['candidate'],
        'organization_id' => $ids['org'],
        'status' => $status,
        'contact_channel' => 'app',
        'profile_access_token' => Str::ulid(),
        'profile_access_expires_at' => now()->addDays(90),
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

it('rejects a second live application from the same candidate', function () {
    $ids = seedJobFixture();

    insertApplication($ids, 'submitted');
    insertApplication($ids, 'submitted');
})->throws(UniqueConstraintViolationException::class);

it('allows re-applying after a withdrawal', function () {
    $ids = seedJobFixture();

    insertApplication($ids, 'withdrawn');
    insertApplication($ids, 'submitted');

    expect(DB::table('applications')->count())->toBe(2);
});

it('rejects an unknown application status', function () {
    $ids = seedJobFixture();
    insertApplication($ids, 'not_a_status');
})->throws(QueryException::class);

it('rejects a salary range whose maximum is below its minimum', function () {
    $ids = seedJobFixture();

    DB::table('jobs')->where('id', $ids['job'])->update([
        'salary_amount' => 5000,
        'salary_amount_max' => 4000,
    ]);
})->throws(QueryException::class);

it('rejects suspending a user without a reason', function () {
    DB::table('users')->insert([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000003', 'role' => 'candidate',
        'status' => 'suspended',
        'created_at' => now(), 'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('frees a phone number once the account is soft deleted', function () {
    DB::table('users')->insert([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000004', 'role' => 'candidate',
        'deleted_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('users')->insert([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000004', 'role' => 'candidate',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    expect(DB::table('users')->where('phone_e164', '+966500000004')->count())->toBe(2);
});

it('allows only one owner per organization', function () {
    $ids = seedJobFixture();

    $second = DB::table('users')->insertGetId([
        'uuid' => Str::uuid(), 'phone_e164' => '+966500000005', 'role' => 'employer',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    DB::table('organization_members')->insert([
        'organization_id' => $ids['org'], 'user_id' => $ids['user'], 'role' => 'owner',
    ]);

    DB::table('organization_members')->insert([
        'organization_id' => $ids['org'], 'user_id' => $second, 'role' => 'owner',
    ]);
})->throws(UniqueConstraintViolationException::class);

it('finds districts within a radius and orders them by distance', function () {
    $olaya = 'ST_SetSRID(ST_MakePoint(46.6853, 24.6944), 4326)::geography';

    $names = DB::select("
        SELECT name->>'ar' AS name
        FROM districts
        WHERE ST_DWithin(center_point, {$olaya}, 5000)
        ORDER BY ST_Distance(center_point, {$olaya})
    ");

    expect($names)->not->toBeEmpty()
        ->and($names[0]->name)->toBe('حي العليا');
});
