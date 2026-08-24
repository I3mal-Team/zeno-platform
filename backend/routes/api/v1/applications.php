<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Applications\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('applications')->name('applications.')->group(function () {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::post('{reference}/withdraw', [ApplicationController::class, 'withdraw'])->name('withdraw');
});
