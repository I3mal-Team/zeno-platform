<?php

declare(strict_types=1);

use App\Models\OtpChallenge;
use App\Models\User;
use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\OtpCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->bind(OtpCodeGenerator::class, fn () => new FixedOtpCodeGenerator('4829'));
});

function requestOtp(string $phone = '0512345678'): TestResponse
{
    return test()->postJson('/api/v1/auth/otp/request', ['phone' => $phone]);
}

function verifyOtp(array $overrides = []): TestResponse
{
    return test()->postJson('/api/v1/auth/otp/verify', array_merge([
        'phone' => '0512345678',
        'code' => '4829',
        'role' => 'candidate',
        'device_name' => 'test',
    ], $overrides));
}

it('issues a code and reports when it expires', function () {
    requestOtp()
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['expires_in_seconds']]);

    expect(OtpChallenge::query()->count())->toBe(1);
});

it('stores the code hashed, never in plain text', function () {
    requestOtp();

    expect(OtpChallenge::query()->value('code_hash'))->not->toBe('4829');
});

it('normalises every accepted phone format to one stored value', function (string $input) {
    requestOtp($input)->assertOk();

    expect(OtpChallenge::query()->value('phone_e164'))->toBe('+966512345678');
})->with([
    '0512345678',
    '512345678',
    '+966512345678',
    '00966512345678',
    '٠٥١٢٣٤٥٦٧٨',
    '05 1234 5678',
]);

it('normalises a number typed for another supported country', function () {
    $this->seed(Database\Seeders\CountrySeeder::class);

    test()->postJson('/api/v1/auth/otp/request', [
        'phone' => '01001234567',
        'country' => 'EG',
    ])->assertOk();

    expect(OtpChallenge::query()->value('phone_e164'))->toBe('+201001234567');
});

it('rejects a number typed for a country the form does not offer', function () {
    test()->postJson('/api/v1/auth/otp/request', [
        'phone' => '0512345678',
        'country' => 'ZZ',
    ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
});

it('rejects a number that is not a Saudi mobile', function (string $input) {
    requestOtp($input)
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED');
})->with(['0412345678', '051234567', '05123456789', 'abcd', '']);

it('creates the account on first successful verification', function () {
    requestOtp();

    verifyOtp()
        ->assertOk()
        ->assertJsonPath('data.is_new_user', true)
        ->assertJsonPath('data.user.role', 'candidate')
        ->assertJsonPath('data.user.phone_verified', true)
        ->assertJsonStructure(['data' => ['token']]);

    expect(User::query()->where('phone_e164', '+966512345678')->exists())->toBeTrue();
});

it('reuses the account on a later sign in', function () {
    requestOtp();
    verifyOtp();

    requestOtp();
    verifyOtp()->assertOk()->assertJsonPath('data.is_new_user', false);

    expect(User::query()->count())->toBe(1);
});

it('rejects a wrong code and counts the attempt', function () {
    requestOtp();

    verifyOtp(['code' => '1111'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'OTP_INVALID');

    expect(OtpChallenge::query()->value('attempts'))->toBe(1);
});

it('burns the code after too many wrong attempts', function () {
    requestOtp();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        verifyOtp(['code' => '1111']);
    }

    verifyOtp(['code' => '4829'])
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'OTP_MAX_ATTEMPTS');
});

it('rejects an expired code', function () {
    requestOtp();

    OtpChallenge::query()->update(['expires_at' => now()->subMinute()]);

    verifyOtp()
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'OTP_EXPIRED');
});

it('will not send a second code during the cooldown', function () {
    requestOtp()->assertOk();

    requestOtp()
        ->assertStatus(429)
        ->assertJsonPath('error.code', 'OTP_RESEND_COOLDOWN')
        ->assertJsonStructure(['error' => ['details' => ['retry_after_seconds']]]);
});

it('consumes the code so it cannot be replayed', function () {
    requestOtp();
    verifyOtp()->assertOk();

    verifyOtp()
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'OTP_INVALID');
});

it('refuses a suspended account before sending anything', function () {
    requestOtp();
    verifyOtp();

    User::query()->update(['status' => 'suspended', 'suspended_reason' => 'test']);

    requestOtp()
        ->assertStatus(403)
        ->assertJsonPath('error.code', 'ACCOUNT_SUSPENDED');
});

it('returns the signed in user', function () {
    requestOtp();
    $token = verifyOtp()->json('data.token');

    test()->withToken($token)->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.phone', '0512345678');
});

it('rejects an unauthenticated request for the current user', function () {
    test()->getJson('/api/v1/auth/me')
        ->assertStatus(401)
        ->assertJsonPath('error.code', 'SESSION_EXPIRED');
});

/**
 * Laravel resolves the guard once per test, not once per request, so a token
 * revoked mid-test still authenticates unless the guard is dropped. Production
 * gets a fresh container per request and needs no equivalent.
 */
function forgetResolvedGuard(): void
{
    app('auth')->forgetGuards();
}

it('invalidates only the current token on logout', function () {
    requestOtp();
    $first = verifyOtp()->json('data.token');

    // Consuming the code clears the pending challenge, so a fresh one may be
    // requested straight away without waiting out the cooldown.
    requestOtp()->assertOk();
    $second = verifyOtp()->json('data.token');

    test()->withToken($first)->postJson('/api/v1/auth/logout')->assertOk();
    forgetResolvedGuard();

    test()->withToken($first)->getJson('/api/v1/auth/me')->assertStatus(401);
    forgetResolvedGuard();

    test()->withToken($second)->getJson('/api/v1/auth/me')->assertOk();
});

it('invalidates every token on logout everywhere', function () {
    requestOtp();
    $token = verifyOtp()->json('data.token');

    test()->withToken($token)->postJson('/api/v1/auth/logout-all')->assertOk();

    expect(User::query()->first()->tokens()->count())->toBe(0);

    forgetResolvedGuard();
    test()->withToken($token)->getJson('/api/v1/auth/me')->assertStatus(401);
});
