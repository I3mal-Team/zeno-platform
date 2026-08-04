<?php

declare(strict_types=1);

use App\Http\Controllers\Site\ApplicationController;
use App\Http\Controllers\Site\Auth\LoginController;
use App\Http\Controllers\Site\DashboardController;
use App\Http\Controllers\Site\MessageController;
use App\Http\Controllers\Site\NotificationController;
use App\Http\Controllers\Site\Profile\CandidateProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'sendCode'])
        ->middleware('throttle:otp-request')
        ->name('login.code');
    Route::get('login/verify', [LoginController::class, 'showVerify'])->name('login.verify');
    Route::post('login/verify', [LoginController::class, 'verify'])
        ->middleware('throttle:otp-verify')
        ->name('login.verify.submit');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('{id}/read', [NotificationController::class, 'markRead'])->name('read');
});

// The candidate's own space on the public site: their applications and chat.
// Employers use the dashboard for the equivalent views instead.
Route::middleware(['auth', 'role:candidate'])->group(function () {
    Route::get('profile/complete', [CandidateProfileController::class, 'show'])->name('profile.complete');
    Route::post('profile/complete', [CandidateProfileController::class, 'store'])->name('profile.complete.store');

    // Everything below needs a completed profile first; the intake above must
    // stay reachable, so the gate wraps only these routes.
    Route::middleware('candidate.profile')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('profile', [CandidateProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [CandidateProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/avatar', [CandidateProfileController::class, 'avatar'])->name('profile.avatar');

        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::get('{uuid}', [MessageController::class, 'show'])->name('show');
            Route::post('{uuid}', [MessageController::class, 'send'])->name('send');
        });

        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/', [ApplicationController::class, 'index'])->name('index');
            Route::post('{reference}/withdraw', [ApplicationController::class, 'withdraw'])->name('withdraw');
        });
    });
});
