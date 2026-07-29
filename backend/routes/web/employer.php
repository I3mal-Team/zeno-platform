<?php

declare(strict_types=1);

use App\Http\Controllers\Employer\ApplicantController;
use App\Http\Controllers\Employer\DashboardController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Employer\JobStatusController;
use App\Http\Controllers\Employer\MessageController;
use App\Http\Controllers\Employer\RegisterController;
use App\Http\Controllers\Employer\VerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('employer')
    ->name('employer.')
    ->middleware(['auth', 'role:employer'])
    ->group(function () {
        Route::get('register', [RegisterController::class, 'show'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{uuid}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::put('jobs/{uuid}', [JobController::class, 'update'])->name('jobs.update');

        Route::post('jobs/{uuid}/pause', [JobStatusController::class, 'pause'])->name('jobs.pause');
        Route::post('jobs/{uuid}/resume', [JobStatusController::class, 'resume'])->name('jobs.resume');
        Route::post('jobs/{uuid}/close', [JobStatusController::class, 'close'])->name('jobs.close');

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{uuid}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{uuid}', [MessageController::class, 'send'])->name('messages.send');

        Route::get('verification', [VerificationController::class, 'show'])->name('verification.show');
        Route::post('verification', [VerificationController::class, 'store'])->name('verification.store');

        Route::get('applicants', [ApplicantController::class, 'index'])->name('applicants.index');
        Route::get('applications/{id}', [ApplicantController::class, 'show'])->name('applicants.show');
        Route::post('applications/{id}/accept', [ApplicantController::class, 'accept'])->name('applicants.accept');
        Route::post('applications/{id}/reject', [ApplicantController::class, 'reject'])->name('applicants.reject');
    });
