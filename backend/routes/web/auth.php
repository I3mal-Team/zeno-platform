<?php

declare(strict_types=1);

use App\Http\Controllers\Site\Auth\LoginController;
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
