<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\OtpController;
use App\Http\Controllers\Api\V1\Auth\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    // Throttled by IP on top of the per-number caps in the service, so one
    // address cannot enumerate numbers.
    Route::middleware('throttle:otp')->group(function () {
        Route::post('otp/request', [OtpController::class, 'request'])->name('otp.request');
        Route::post('otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [SessionController::class, 'me'])->name('me');
        Route::post('logout', [SessionController::class, 'logout'])->name('logout');
        Route::post('logout-all', [SessionController::class, 'logoutEverywhere'])->name('logout.all');
    });
});
