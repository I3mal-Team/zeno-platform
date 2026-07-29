<?php

declare(strict_types=1);

use App\Http\Controllers\Site\Jobs\ApplyController;
use App\Http\Controllers\Site\Jobs\JobIndexController;
use App\Http\Controllers\Site\Jobs\JobShowController;
use App\Http\Controllers\Site\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/', JobIndexController::class)->name('index');
    Route::get('{slug}', JobShowController::class)->name('show');
    Route::post('{slug}/apply', ApplyController::class)
        ->middleware(['auth', 'role:candidate', 'candidate.profile'])
        ->name('apply');
});

// Reporting needs an account so a complaint is attributable; the throttle keeps
// one signed-in person from flooding the moderation queue.
Route::post('report/{target}/{id}', ReportController::class)
    ->middleware(['auth', 'throttle:report'])
    ->name('report');
