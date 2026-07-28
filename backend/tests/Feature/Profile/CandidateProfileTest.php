<?php

declare(strict_types=1);

use App\Models\CandidateProfile;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function candidate(): User
{
    return User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => '+966512345678',
        'role' => 'candidate',
        'status' => 'active',
    ]);
}

function profilePayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'سعود الحربي',
        'city_id' => City::query()->value('id'),
        'job_title' => 'باريستا',
        'years_of_experience' => 3,
        'skills' => ['تحضير القهوة', 'خدمة العملاء'],
        'bio' => 'باريستا متمرّس بخبرة ثلاث سنوات.',
        'birth_date' => '2000-01-15',
    ], $overrides);
}

it('rejects an unauthenticated request', function () {
    test()->getJson('/api/v1/profile/candidate')->assertStatus(401);
});

it('reports no profile before one is created', function () {
    test()->actingAs(candidate(), 'sanctum')
        ->getJson('/api/v1/profile/candidate')
        ->assertStatus(404)
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('saves a profile and returns it', function () {
    test()->actingAs(candidate(), 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload())
        ->assertOk()
        ->assertJsonPath('data.full_name', 'سعود الحربي')
        ->assertJsonPath('data.job_title', 'باريستا')
        ->assertJsonCount(2, 'data.skills');
});

it('accepts the mobile intake payload with id type and nationality', function () {
    // Mirrors exactly what the reworked "أكمل بياناتك" mobile form PUTs:
    // a client-derived birth_date and national_id_type plus a nationality code.
    test()->actingAs(candidate(), 'sanctum')
        ->putJson('/api/v1/profile/candidate', [
            'full_name' => 'سالم العتيبي',
            'national_id' => '1012345678',
            'national_id_type' => 'national',
            'birth_date' => '1998-07-28',
            'nationality_code' => 'SA',
            'city_id' => City::query()->value('id'),
            'job_title' => 'باريستا',
            'years_of_experience' => 4,
            'skills' => [],
        ])
        ->assertOk()
        ->assertJsonPath('data.full_name', 'سالم العتيبي');

    $profile = CandidateProfile::query()->first();

    expect($profile->national_id_type)->toBe('national');
    expect($profile->nationality_code)->toBe('SA');
    expect($profile->national_id)->toBe('1012345678');
});

it('derives age from the birth date rather than storing it', function () {
    test()->actingAs(candidate(), 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload(['birth_date' => '2000-01-15']))
        ->assertOk()
        ->assertJsonPath('data.age', Carbon::parse('2000-01-15')->age);
});

it('scores completion higher as more fields are filled', function () {
    $user = candidate();

    $sparse = test()->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/candidate', ['full_name' => 'سعود الحربي'])
        ->json('data.completion_percentage');

    $full = test()->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload())
        ->json('data.completion_percentage');

    expect($sparse)->toBeLessThan($full)
        ->and($full)->toBe(100);
});

it('updates in place rather than creating a second profile', function () {
    $user = candidate();

    test()->actingAs($user, 'sanctum')->putJson('/api/v1/profile/candidate', profilePayload());
    test()->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload(['job_title' => 'كاشير']))
        ->assertOk()
        ->assertJsonPath('data.job_title', 'كاشير');

    expect(CandidateProfile::query()->count())->toBe(1);
});

it('encrypts the national id at rest and never returns it', function () {
    test()->actingAs(candidate(), 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload(['national_id' => '1234567890']))
        ->assertOk()
        ->assertJsonMissingPath('data.national_id');

    $stored = DB::table('candidate_profiles')->value('national_id');

    expect($stored)->not->toBe('1234567890');
    expect(CandidateProfile::query()->first()->national_id)->toBe('1234567890');
});

it('rejects an invalid profile', function (array $payload) {
    test()->actingAs(candidate(), 'sanctum')
        ->putJson('/api/v1/profile/candidate', profilePayload($payload))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
})->with([
    'name too short' => [['full_name' => 'ا']],
    'under age' => [['birth_date' => '2020-01-01']],
    'impossible experience' => [['years_of_experience' => 90]],
    'unknown city' => [['city_id' => 99999]],
    'national id wrong length' => [['national_id' => '123']],
]);

it('stores an avatar and returns its url', function () {
    Storage::fake('public');

    $user = candidate();
    test()->actingAs($user, 'sanctum')->putJson('/api/v1/profile/candidate', profilePayload());

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/profile/candidate/avatar', [
            'avatar' => UploadedFile::fake()->image('me.jpg', 400, 400),
        ])
        ->assertOk()
        ->assertJsonPath('data.avatar_url', fn ($url) => is_string($url) && $url !== '');
});

it('rejects a file that is not an image', function () {
    $user = candidate();
    test()->actingAs($user, 'sanctum')->putJson('/api/v1/profile/candidate', profilePayload());

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/profile/candidate/avatar', [
            'avatar' => UploadedFile::fake()->create('cv.pdf', 100),
        ])
        ->assertStatus(422);
});
