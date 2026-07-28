<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Enums\VerificationStatus;
use App\Models\City;
use App\Models\Organization;
use App\Models\User;
use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\OtpCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    app()->bind(OtpCodeGenerator::class, fn () => new FixedOtpCodeGenerator('4829'));
});

/** Runs the two-step web OTP flow and returns the final response. */
function webLogin(string $phone, string $role, string $country = 'SA'): TestResponse
{
    test()->post('/login', ['phone' => $phone, 'role' => $role, 'country' => $country]);

    return test()->post('/login/verify', ['code' => '4829']);
}

function userWithPhone(string $e164, string $role): User
{
    return User::query()->create([
        'uuid' => (string) Str::uuid(),
        'phone_e164' => $e164,
        'role' => $role,
        'status' => 'active',
    ]);
}

it('redirects a guest away from the employer area to login', function () {
    test()->get('/employer')->assertRedirect('/login');
});

it('signs a new employer in and sends them to registration', function () {
    webLogin('0512345678', 'employer')->assertRedirect(route('employer.register'));

    test()->assertAuthenticated();
    expect(User::query()->where('phone_e164', '+966512345678')->value('role'))->toBe('employer');
});

it('sends a new candidate to complete their profile', function () {
    webLogin('0512345000', 'candidate')->assertRedirect(route('profile.complete'));

    test()->assertAuthenticated();
});

it('sends a returning candidate who already has a profile straight home', function () {
    $user = userWithPhone('+966512345000', 'candidate');
    $user->candidateProfile()->create([
        'full_name' => 'سالم العتيبي',
        'completion_percentage' => 60,
    ]);

    webLogin('0512345000', 'candidate')->assertRedirect(route('site.home'));

    test()->assertAuthenticated();
});

it('rejects a wrong code and keeps the visitor unauthenticated', function () {
    test()->post('/login', ['phone' => '0512345678', 'role' => 'employer', 'country' => 'SA']);

    test()->post('/login/verify', ['code' => '0000'])->assertRedirect();
    test()->assertGuest();
});

it('normalises an egyptian number to its E.164 form', function () {
    webLogin('01012345678', 'candidate', 'EG');

    test()->assertAuthenticated();
    expect(User::query()->value('phone_e164'))->toBe('+201012345678');
});

it('rejects a number that is invalid for its country', function () {
    test()->post('/login', ['phone' => '12345', 'role' => 'candidate', 'country' => 'SA'])
        ->assertSessionHasErrors('phone');
});

it('rejects an unknown country', function () {
    test()->post('/login', ['phone' => '0512345678', 'role' => 'candidate', 'country' => 'ZZ'])
        ->assertSessionHasErrors('country');
});

it('keeps a candidate out of the employer area', function () {
    webLogin('0512345000', 'candidate');

    test()->get('/employer')->assertRedirect(route('site.home'));
});

it('lets a registered employer reach the dashboard', function () {
    $user = userWithPhone('+966512345678', 'employer');
    $organization = Organization::query()->create([
        'uuid' => (string) Str::uuid(),
        'type' => 'company',
        'name' => 'منشأة',
        'slug' => 'org-'.Str::lower(Str::random(6)),
        'city_id' => City::query()->value('id'),
        'verification_status' => VerificationStatus::Verified->value,
        'status' => 'active',
    ]);
    $organization->members()->attach($user->id, [
        'role' => OrganizationRole::Owner->value,
        'joined_at' => now(),
    ]);

    webLogin('0512345678', 'employer');

    test()->get('/employer')->assertOk()->assertSee('منشأة');
});

it('requires a sign-in to apply for a job', function () {
    test()->post('/jobs/some-slug/apply')->assertRedirect('/login');
    test()->assertGuest();
});

/**
 * Drains the send-code limiter and returns the response that tripped it. The
 * count only has to clear the `otp-request` per-minute cap; the callers assert
 * the throttle actually fired, so a raised cap fails loudly rather than
 * quietly turning these into no-ops.
 */
function exhaustLoginRequests(): TestResponse
{
    for ($i = 0; $i < 15; $i++) {
        $response = test()->post('/login', ['phone' => '0512345678', 'role' => 'candidate', 'country' => 'SA']);
    }

    return $response;
}

it('does not spend the verify budget on requesting a code', function () {
    // An inline `throttle:6,1` keys on IP alone, so both steps drew on one
    // allowance and a single sign-in cost two of it. Draining the request
    // limit must leave the visitor able to finish verifying.
    exhaustLoginRequests()->assertSessionHasErrors(['form' => __('errors.rate_limited')]);

    // Where a fresh sign-in lands is the profile flow's business; all this
    // test needs is that verifying still went through.
    test()->post('/login/verify', ['code' => '4829'])->assertRedirect();
    test()->assertAuthenticated();
});

it('returns a throttled visitor to the form instead of a bare 429 page', function () {
    $response = exhaustLoginRequests();

    $response->assertRedirect()->assertSessionHasErrors(['form' => __('errors.rate_limited')]);
    expect($response->getStatusCode())->not->toBe(429);
});
