<?php

declare(strict_types=1);

use App\Models\City;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function employer(): User
{
    return User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => '+966555555555',
        'role' => 'employer',
        'status' => 'active',
    ]);
}

function organizationPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'company',
        'name' => 'كافيه ميلاج',
        'responsible_person_name' => 'خالد العتيبي',
        'commercial_registration' => '1010442318',
        'city_id' => City::query()->value('id'),
        'about' => 'سلسلة مقاهي متخصّصة في القهوة المختصة.',
    ], $overrides);
}

it('creates an organisation and makes the creator its owner', function () {
    $user = employer();

    test()->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/employer', organizationPayload())
        ->assertOk()
        ->assertJsonPath('data.name', 'كافيه ميلاج')
        ->assertJsonPath('data.verification_status', 'unverified')
        ->assertJsonPath('data.is_verified', false);

    $organization = Organization::query()->first();

    expect($organization->members()->count())->toBe(1)
        ->and($organization->members->first()->pivot->role)->toBe('owner');
});

it('creates an organisation for an individual employer too', function () {
    test()->actingAs(employer(), 'sanctum')
        ->putJson('/api/v1/profile/employer', organizationPayload([
            'type' => 'individual',
            'commercial_registration' => null,
        ]))
        ->assertOk()
        ->assertJsonPath('data.type', 'individual');
});

it('requires a commercial registration from a company', function () {
    test()->actingAs(employer(), 'sanctum')
        ->putJson('/api/v1/profile/employer', organizationPayload([
            'commercial_registration' => null,
        ]))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('encrypts the commercial registration and never returns it', function () {
    test()->actingAs(employer(), 'sanctum')
        ->putJson('/api/v1/profile/employer', organizationPayload())
        ->assertJsonMissingPath('data.commercial_registration');

    expect(DB::table('organizations')->value('commercial_registration'))
        ->not->toBe('1010442318');
});

it('updates in place rather than creating a second organisation', function () {
    $user = employer();

    test()->actingAs($user, 'sanctum')->putJson('/api/v1/profile/employer', organizationPayload());
    test()->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/employer', organizationPayload(['name' => 'ميلاج للقهوة']))
        ->assertOk()
        ->assertJsonPath('data.name', 'ميلاج للقهوة');

    expect(Organization::query()->count())->toBe(1);
});

it('gives an Arabic name a usable unique slug', function () {
    test()->actingAs(employer(), 'sanctum')->putJson('/api/v1/profile/employer', organizationPayload());

    expect(Organization::query()->value('slug'))->not->toBeEmpty();
});

it('does not expose another employer organisation', function () {
    test()->actingAs(employer(), 'sanctum')->putJson('/api/v1/profile/employer', organizationPayload());

    $other = User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => '+966566666666',
        'role' => 'employer',
        'status' => 'active',
    ]);

    test()->actingAs($other, 'sanctum')
        ->getJson('/api/v1/profile/employer')
        ->assertStatus(404);
});
