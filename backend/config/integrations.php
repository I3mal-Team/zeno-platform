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
        // untouched.
        //
        // Hardcoded rather than read only from the environment because the
        // production .env is not ours to edit, and sign-in is the only way into
        // the app: an env-only switch means no reviewer bypass at all there.
        // `?:` rather than a second argument to env(), because an
        // OTP_REVIEW_ACCOUNTS= line already sitting empty in a server .env would
        // otherwise win and silently disable the bypass.
        //
        // These pairs are a standing credential: anyone with the repository can
        // sign in as these accounts. Keep them free of real personal data, and
        // change them once the review is done. To turn the bypass off without a
        // code change, set OTP_REVIEW_ACCOUNTS to a value that parses to no
        // pairs, e.g. "off".
        'review_accounts' => env('OTP_REVIEW_ACCOUNTS') ?: '+966533333332:4829,+966533333335:4829',
    ],

];
