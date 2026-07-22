<?php

declare(strict_types=1);

use App\Http\Controllers\Employer\DashboardController;
use App\Http\Controllers\Employer\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('employer')
    ->name('employer.')
    ->middleware(['auth', 'role:employer'])
    ->group(function () {
        Route::get('register', [RegisterController::class, 'show'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');
        Route::get('/', DashboardController::class)->name('dashboard');
    });
