<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Billing\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('billing')->name('billing.')->group(function () {
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('subscription', [PlanController::class, 'current'])->name('subscription.current');
});
