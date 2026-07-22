<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Notifications\DeviceTokenController;
use App\Http\Controllers\Api\V1\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread');
    Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::post('{id}/read', [NotificationController::class, 'markRead'])->name('read');

    Route::post('devices', [DeviceTokenController::class, 'store'])->name('devices.store');
    Route::delete('devices', [DeviceTokenController::class, 'destroy'])->name('devices.destroy');
});
