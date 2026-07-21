<?php

declare(strict_types=1);

use App\Settings\OtpSettings;
use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\OtpCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->bind(OtpCodeGenerator::class, fn () => new FixedOtpCodeGenerator('4829'));
});

it('drives the code lifetime from settings, not a constant', function () {
    $settings = app(OtpSettings::class);
    $settings->expiry_minutes = 12;
    $settings->save();

    $expiresIn = test()
        ->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])
        ->json('data.expires_in_seconds');

    expect($expiresIn)->toBeGreaterThan(700)->toBeLessThanOrEqual(720);
});

it('drives the resend cooldown from settings', function () {
    $settings = app(OtpSettings::class);
    $settings->resend_cooldown_seconds = 300;
    $settings->save();

    test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678']);

    $retryAfter = test()
        ->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678'])
        ->assertStatus(429)
        ->json('error.details.retry_after_seconds');

    expect($retryAfter)->toBeGreaterThan(290);
});

it('drives the attempt cap from settings', function () {
    $settings = app(OtpSettings::class);
    $settings->max_attempts = 2;
    $settings->save();

    test()->postJson('/api/v1/auth/otp/request', ['phone' => '0512345678']);

    $wrong = ['phone' => '0512345678', 'code' => '1111', 'role' => 'candidate'];

    test()->postJson('/api/v1/auth/otp/verify', $wrong);
    test()->postJson('/api/v1/auth/otp/verify', $wrong);

    test()->postJson('/api/v1/auth/otp/verify', [
        'phone' => '0512345678', 'code' => '4829', 'role' => 'candidate',
    ])->assertStatus(429)->assertJsonPath('error.code', 'OTP_MAX_ATTEMPTS');
});
