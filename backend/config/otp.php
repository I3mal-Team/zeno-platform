<?php

declare(strict_types=1);

return [
    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),

    /** Attempts against one code before it is burned and a new one is needed. */
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

    'resend_cooldown_seconds' => env('OTP_RESEND_COOLDOWN', 60),

    /** Requests allowed per phone number inside the window below. */
    'max_requests_per_window' => env('OTP_MAX_REQUESTS', 5),

    'window_minutes' => env('OTP_WINDOW_MINUTES', 60),
];
