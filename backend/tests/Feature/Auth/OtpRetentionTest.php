<?php

declare(strict_types=1);

use App\Models\OtpChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * A sign-in challenge stores the phone number, the caller's IP and their user
 * agent. The privacy policy commits to clearing that after a window, and this
 * command is the only thing that makes the commitment true — the repository
 * method existed for a while with nothing ever calling it.
 */
function challengeExpiredDaysAgo(int $days): OtpChallenge
{
    return OtpChallenge::query()->create([
        'phone_e164' => '+9665'.str_pad((string) $days, 8, '0', STR_PAD_LEFT),
        'code_hash' => Hash::make('4829'),
        'purpose' => 'login',
        'expires_at' => now()->subDays($days),
        'ip_address' => '81.10.0.1',
        'user_agent' => 'Zeno/1.0',
    ]);
}

it('clears challenges past the retention window', function () {
    challengeExpiredDaysAgo(40);

    test()->artisan('otp:purge')->assertSuccessful();

    expect(OtpChallenge::query()->count())->toBe(0);
});

it('keeps challenges still inside the window', function () {
    challengeExpiredDaysAgo(5);

    test()->artisan('otp:purge')->assertSuccessful();

    expect(OtpChallenge::query()->count())->toBe(1);
});

it('takes a shorter window when asked for one', function () {
    challengeExpiredDaysAgo(5);

    test()->artisan('otp:purge', ['--days' => 2])->assertSuccessful();

    expect(OtpChallenge::query()->count())->toBe(0);
});

it('leaves a live code alone', function () {
    OtpChallenge::query()->create([
        'phone_e164' => '+966512345678',
        'code_hash' => Hash::make('4829'),
        'purpose' => 'login',
        'expires_at' => now()->addMinutes(10),
    ]);

    test()->artisan('otp:purge')->assertSuccessful();

    expect(OtpChallenge::query()->count())->toBe(1);
});
