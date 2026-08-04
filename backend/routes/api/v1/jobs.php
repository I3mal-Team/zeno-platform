<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Applications\ApplicationController;
use App\Http\Controllers\Api\V1\Jobs\JobBrowseController;
use App\Http\Controllers\Api\V1\Jobs\SavedJobController;
use Illuminate\Support\Facades\Route;

Route::get('saved-jobs', [SavedJobController::class, 'index'])
    ->middleware('auth:sanctum')->name('saved-jobs');

Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/', [JobBrowseController::class, 'index'])->name('index');
    Route::get('{slug}', [JobBrowseController::class, 'show'])->name('show');
    Route::post('{slug}/apply', [ApplicationController::class, 'store'])
        ->middleware('auth:sanctum')->name('apply');
    Route::post('{slug}/save', [SavedJobController::class, 'store'])
        ->middleware('auth:sanctum')->name('save');
    Route::delete('{slug}/save', [SavedJobController::class, 'destroy'])
        ->middleware('auth:sanctum')->name('unsave');
});
