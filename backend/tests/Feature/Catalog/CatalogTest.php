<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('lists the active dial codes for the sign-in form', function () {
    test()->getJson('/api/v1/countries')
        ->assertOk()
        ->assertJsonPath('data.0.iso2', 'SA')
        ->assertJsonPath('data.0.dial_code', '+966')
        ->assertJsonPath('data.0.placeholder', '5X XXX XXXX')
        ->assertJsonPath('data.1.iso2', 'EG');
});

it('lists categories without a session', function () {
    test()->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('data.0.code', 'restaurants');
});

it('lists cities', function () {
    test()->getJson('/api/v1/cities')
        ->assertOk()
        ->assertJsonPath('data.0.code', 'riyadh')
        ->assertJsonPath('data.0.name', 'الرياض');
});

it('lists the districts of a city', function () {
    $riyadh = test()->getJson('/api/v1/cities')->json('data.0.id');

    test()->getJson("/api/v1/cities/{$riyadh}/districts")
        ->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonPath('data.0.name', 'حي العليا');
});

it('returns an empty list for a city with no districts', function () {
    $cities = test()->getJson('/api/v1/cities')->json('data');

    test()->getJson('/api/v1/cities/'.collect($cities)->last()['id'].'/districts')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
