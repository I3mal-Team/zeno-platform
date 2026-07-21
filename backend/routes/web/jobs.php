<?php

declare(strict_types=1);

use App\Http\Controllers\Site\Jobs\JobIndexController;
use App\Http\Controllers\Site\Jobs\JobShowController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/', JobIndexController::class)->name('index');
    Route::get('{slug}', JobShowController::class)->name('show');
});
