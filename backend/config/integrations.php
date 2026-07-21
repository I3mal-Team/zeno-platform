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

    'otp' => [
        'generator' => env('OTP_GENERATOR', 'random'),

        'generators' => [
            'random' => RandomOtpCodeGenerator::class,
            'fixed' => FixedOtpCodeGenerator::class,
        ],

        // Fixed at 4 because the mobile OTP screen renders exactly four boxes.
        'length' => 4,

        'fixed_code' => env('OTP_FIXED_CODE', '4829'),
    ],

];
