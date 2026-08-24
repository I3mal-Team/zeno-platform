<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Code length is deliberately absent: the mobile screen renders a fixed number
 * of boxes, so changing it from an admin form would break the app.
 */
class OtpSettings extends Settings
{
    public int $expiry_minutes;

    public int $max_attempts;

    public int $resend_cooldown_seconds;

    public int $max_requests_per_window;

    public int $window_minutes;

    public static function group(): string
    {
        return 'otp';
    }
}
