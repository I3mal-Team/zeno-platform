<?php

declare(strict_types=1);

use App\Http\Controllers\Employer\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('employer')->name('employer.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
});
