<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MediaSettings extends Settings
{
    public int $max_upload_kb;

    public static function group(): string
    {
        return 'media';
    }
}
