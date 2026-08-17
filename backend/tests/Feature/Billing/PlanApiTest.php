<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('requires authentication to list plans', function () {
    test()->getJson('/api/v1/billing/plans')->assertUnauthorized();
});

it('shows an employer only employer plans, cheapest first', function () {
    [$user] = makeEmployerWithOrg();

    $response = test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/billing/plans')
        ->assertOk();

    $plans = $response->json('data');
    $audiences = array_unique(array_column($plans, 'audience'));
    $prices = array_column($plans, 'price');

    expect($audiences)->toBe(['employer'])
        ->and($prices)->toBe(array_values(collect($prices)->sort()->all()))
        ->and($plans[0]['is_free'])->toBeTrue()
        ->and(collect($plans[0]['features'])->pluck('key'))->toContain('active_listings_limit');
});

it('shows a candidate only candidate plans', function () {
    $user = makeUser('candidate');

    $audiences = test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/billing/plans')
        ->assertOk()
        ->json('data.*.audience');

    expect(array_unique($audiences))->toBe(['candidate']);
});

it('flags the plan the employer is currently subscribed to', function () {
    [$user] = makeEmployerWithOrg();
    subscribeEmployer($user, 'employer_pro');

    $plans = test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/billing/plans')
        ->assertOk()
        ->json('data');

    $current = collect($plans)->firstWhere('is_current', true);

    expect($current)->not->toBeNull()
        ->and($current['code'])->toBe('employer_pro');
});

it('returns the live subscription and effective plan for a subscriber', function () {
    [$user] = makeEmployerWithOrg();
    subscribeEmployer($user, 'employer_basic');

    test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.subscription.status', 'active')
        ->assertJsonPath('data.subscription.is_live', true)
        ->assertJsonPath('data.subscription.plan.code', 'employer_basic')
        ->assertJsonPath('data.plan.code', 'employer_basic')
        ->assertJsonPath('data.plan.is_current', true);
});

it('returns no subscription and the free plan for a free-tier user', function () {
    [$user] = makeEmployerWithOrg();

    test()->actingAs($user, 'sanctum')
        ->getJson('/api/v1/billing/subscription')
        ->assertOk()
        ->assertJsonPath('data.subscription', null)
        ->assertJsonPath('data.plan.is_free', true);
});
