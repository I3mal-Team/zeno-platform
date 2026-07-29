<?php

declare(strict_types=1);

use App\Enums\JobStatus;
use App\Enums\OrganizationRole;
use App\Enums\VerificationStatus;
use App\Models\City;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/** The id of a seeded reference row, looked up by its code. */
function refId(string $table, string $code): int
{
    return (int) DB::table($table)->where('code', $code)->value('id');
}

function makeUser(string $role = 'employer', ?string $phone = null): User
{
    $user = User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => $phone ?? '+9665'.fake()->numerify('########'),
        'role' => $role,
        'status' => 'active',
    ]);

    // A candidate can only apply or message once their profile exists (the
    // `candidate.profile` gate), so the shared helper gives them one.
    if ($role === 'candidate') {
        $user->candidateProfile()->create([
            'full_name' => 'مرشّح اختبار',
            'completion_percentage' => 60,
        ]);
    }

    return $user;
}

/**
 * An employer with an organization, owned by the returned user.
 *
 * @return array{0: User, 1: Organization}
 */
function makeEmployerWithOrg(bool $verified = false, ?User $user = null): array
{
    $user ??= makeUser('employer');

    $organization = Organization::query()->create([
        'uuid' => (string) Str::uuid(),
        'type' => 'company',
        'name' => 'منشأة اختبار',
        'slug' => 'test-'.Str::lower(Str::random(6)),
        'city_id' => City::query()->value('id'),
        'verification_status' => $verified ? VerificationStatus::Verified->value : VerificationStatus::Unverified->value,
        'status' => 'active',
    ]);

    $organization->members()->attach($user->id, [
        'role' => OrganizationRole::Owner->value,
        'joined_at' => now(),
    ]);

    return [$user, $organization];
}

/**
 * A live, published listing owned by [$org].
 *
 * @param  array<string, mixed>  $overrides
 */
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

/** @return array<string, mixed> */
function jobPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'كاشير دوام كامل',
        'description' => 'مطلوب كاشير للعمل في فرع الرياض.',
        'category_id' => (int) DB::table('categories')->value('id'),
        'work_type_id' => refId('work_types', 'full_time'),
        'salary_unit_id' => refId('salary_units', 'monthly'),
        'gender_requirement_id' => refId('gender_requirements', 'all'),
        'nationality_requirement_id' => refId('nationality_requirements', 'all'),
        'salary_amount' => 4500,
        'vacancies_count' => 2,
        'city_id' => (int) City::query()->value('id'),
        'contact_channel' => 'app',
    ], $overrides);
}
