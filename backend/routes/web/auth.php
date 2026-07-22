<?php

declare(strict_types=1);

use App\Http\Controllers\Site\Auth\LoginController;
use App\Http\Controllers\Site\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'sendCode'])
        ->middleware('throttle:6,1')
        ->name('login.code');
    Route::get('login/verify', [LoginController::class, 'showVerify'])->name('login.verify');
    Route::post('login/verify', [LoginController::class, 'verify'])
        ->middleware('throttle:6,1')
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
