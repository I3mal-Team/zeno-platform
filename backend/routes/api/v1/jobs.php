<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Applications\ApplicationController;
use App\Http\Controllers\Api\V1\Jobs\JobBrowseController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/', [JobBrowseController::class, 'index'])->name('index');
    Route::get('{slug}', [JobBrowseController::class, 'show'])->name('show');
    Route::post('{slug}/apply', [ApplicationController::class, 'store'])
        ->middleware('auth:sanctum')->name('apply');
});
