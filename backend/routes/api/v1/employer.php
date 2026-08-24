<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Employer\ApplicantController;
use App\Http\Controllers\Api\V1\Employer\JobController;
use App\Http\Controllers\Api\V1\Employer\JobStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('employer')->name('employer.')->group(function () {
    Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('jobs/{uuid}', [JobController::class, 'show'])->name('jobs.show');
    Route::put('jobs/{uuid}', [JobController::class, 'update'])->name('jobs.update');

    Route::post('jobs/{uuid}/pause', [JobStatusController::class, 'pause'])->name('jobs.pause');
    Route::post('jobs/{uuid}/resume', [JobStatusController::class, 'resume'])->name('jobs.resume');
    Route::post('jobs/{uuid}/fill', [JobStatusController::class, 'fill'])->name('jobs.fill');
    Route::post('jobs/{uuid}/close', [JobStatusController::class, 'close'])->name('jobs.close');

    Route::get('applications', [ApplicantController::class, 'organizationIndex'])->name('applicants.organization');
    Route::get('jobs/{uuid}/applications', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('applications/{id}', [ApplicantController::class, 'show'])->name('applicants.show');
    Route::post('applications/{id}/accept', [ApplicantController::class, 'accept'])->name('applicants.accept');
    Route::post('applications/{id}/reject', [ApplicantController::class, 'reject'])->name('applicants.reject');
    Route::post('applications/{id}/shortlist', [ApplicantController::class, 'shortlist'])->name('applicants.shortlist');
    Route::put('applications/{id}/note', [ApplicantController::class, 'note'])->name('applicants.note');
});
