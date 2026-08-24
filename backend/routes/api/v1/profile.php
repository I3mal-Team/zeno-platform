<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Profile\AvatarController;
use App\Http\Controllers\Api\V1\Profile\CandidateProfileController;
use App\Http\Controllers\Api\V1\Profile\OrganizationController;
use App\Http\Controllers\Api\V1\Profile\ResumeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('profile')->name('profile.')->group(function () {
    Route::get('candidate', [CandidateProfileController::class, 'show'])->name('candidate.show');
    Route::put('candidate', [CandidateProfileController::class, 'save'])->name('candidate.save');
    Route::post('candidate/avatar', [AvatarController::class, 'store'])->name('candidate.avatar');
    Route::post('candidate/resume', [ResumeController::class, 'store'])->name('candidate.resume');

    Route::get('employer', [OrganizationController::class, 'show'])->name('employer.show');
    Route::put('employer', [OrganizationController::class, 'save'])->name('employer.save');
    Route::post('employer/logo', [OrganizationController::class, 'logo'])->name('employer.logo');
});
