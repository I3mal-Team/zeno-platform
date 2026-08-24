<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class JobSettings extends Settings
{
    public int $default_duration_days;

    public int $max_duration_days;

    public int $expiry_warning_days;

    public static function group(): string
    {
        return 'job';
    }
}
