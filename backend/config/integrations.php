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

        // One allow-listed number that signs in with a constant code and is
        // never sent an SMS, because App Store and Play reviewers test from a
        // device that cannot receive one — the single most common cause of a
        // phone-OTP app being rejected.
        //
        // Unlike 'generator' => 'fixed', this affects exactly one number, so
        // every real account keeps a random code. It is meant to be ON in
        // production; leave both blank to disable. Never point it at a number
        // a real person uses.
        'review_account' => [
            'phone_e164' => env('AUTH_REVIEW_PHONE'),
            'code' => env('AUTH_REVIEW_OTP'),
        ],
    ],

];
