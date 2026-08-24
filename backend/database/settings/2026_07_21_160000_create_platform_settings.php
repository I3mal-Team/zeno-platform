<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('otp.expiry_minutes', 5);
        $this->migrator->add('otp.max_attempts', 5);
        $this->migrator->add('otp.resend_cooldown_seconds', 60);
        $this->migrator->add('otp.max_requests_per_window', 5);
        $this->migrator->add('otp.window_minutes', 60);

        $this->migrator->add('job.default_duration_days', 30);
        $this->migrator->add('job.max_duration_days', 90);
        $this->migrator->add('job.expiry_warning_days', 3);

        $this->migrator->add('media.max_upload_kb', 5120);
    }
};
