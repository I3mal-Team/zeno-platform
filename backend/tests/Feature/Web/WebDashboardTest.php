<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('shows the dashboard overview to a candidate with a profile', function () {
    test()->actingAs(makeUser('candidate'))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('إجمالي الطلبات')
        ->assertSee('رسائلك');
});

it('sends an incomplete candidate from the dashboard to the profile intake', function () {
    $candidate = User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => '+966512348888',
        'role' => 'candidate',
        'status' => 'active',
    ]);

    test()->actingAs($candidate)
        ->get(route('dashboard'))
        ->assertRedirect(route('profile.complete'));
});

it('requires a sign-in for the dashboard', function () {
    test()->get(route('dashboard'))->assertRedirect(route('login'));
});

it('keeps an employer out of the candidate dashboard', function () {
    test()->actingAs(makeUser('employer'))
        ->get(route('dashboard'))
        ->assertRedirect(route('site.home'));
});
