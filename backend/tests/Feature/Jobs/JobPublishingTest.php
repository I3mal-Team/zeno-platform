<?php

declare(strict_types=1);

use App\Models\City;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('publishes a verified organisation listing straight to active', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.is_open_for_applications', true);

    expect(Job::query()->value('published_at'))->not->toBeNull();
});

it('sends an unverified organisation first listing to review', function () {
    [$user] = makeEmployerWithOrg(verified: false);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending_review')
        ->assertJsonPath('data.is_open_for_applications', false);

    expect(Job::query()->value('published_at'))->toBeNull();
});

it('records the initial status in the job history', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')->postJson('/api/v1/employer/jobs', jobPayload());

    expect(DB::table('job_status_history')->where('to_status', 'active')->count())->toBe(1);
});

it('refuses to publish when the employer has no organisation', function () {
    $user = makeUser('employer');

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload())
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'ORGANIZATION_MISSING');
});

it('rejects a district that does not belong to the chosen city', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    $district = DB::table('districts')->first();
    $otherCity = City::query()->where('id', '!=', $district->city_id)->value('id');

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['city_id' => $otherCity, 'district_id' => $district->id]))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('validates required fields', function () {
    [$user] = makeEmployerWithOrg(verified: true);

    test()->actingAs($user, 'sanctum')
        ->postJson('/api/v1/employer/jobs', jobPayload(['title' => '']))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('lists only the employers own listings', function () {
    [$user] = makeEmployerWithOrg(verified: true);
    test()->actingAs($user, 'sanctum')->postJson('/api/v1/employer/jobs', jobPayload());

    [$other] = makeEmployerWithOrg(verified: true);
    test()->actingAs($other, 'sanctum')->postJson('/api/v1/employer/jobs', jobPayload());

    test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/employer/jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
