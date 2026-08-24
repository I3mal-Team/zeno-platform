<?php

declare(strict_types=1);

use App\Support\Otp\FixedOtpCodeGenerator;
use App\Support\Otp\RandomOtpCodeGenerator;
use App\Support\Sms\LogSmsGateway;

return [

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),

        'drivers' => [
            'log' => LogSmsGateway::class,
        ],
    ],

    // D-01: applying is free at launch. Flip this on (and build billing) when a
    // paid candidate plan is introduced; SubscriptionService already gates on it.
    'candidate_subscription_required' => env('CANDIDATE_SUBSCRIPTION_REQUIRED', false),

    'jobs' => [
        // TEMPORARY: publish every new listing straight to active, skipping the
        // first-listing moderation gate (D-15). Set JOBS_AUTO_PUBLISH_ALL=false
        // (or remove this) to restore the review queue for unverified employers.
        'auto_publish_all' => (bool) env('JOBS_AUTO_PUBLISH_ALL', true),
    ],

    'otp' => [
        'generator' => env('OTP_GENERATOR', 'random'),

        'generators' => [
            'random' => RandomOtpCodeGenerator::class,
            'fixed' => FixedOtpCodeGenerator::class,
        ],

        // Fixed at 4 because the mobile OTP screen renders exactly four boxes.
        'length' => 4,

        'fixed_code' => env('OTP_FIXED_CODE', '4829'),

        // Store-reviewer sign-in. Format: "+966500000001:1234,+966500000002:5678".
        // Listed numbers get that code and no SMS; every other number is
        // untouched. Empty by default — nothing is bypassed unless set.
        'review_accounts' => env('OTP_REVIEW_ACCOUNTS', ''),
    ],

];
