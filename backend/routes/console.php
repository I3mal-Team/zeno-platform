<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Enforces the retention window the privacy policy commits to. Runs off-peak
// because it is a bulk delete on a table every sign-in writes to.
Schedule::command('otp:purge')->dailyAt('03:30');
