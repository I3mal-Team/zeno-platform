<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Messaging\ConversationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', [ConversationController::class, 'index'])->name('index');
    Route::get('{uuid}/messages', [ConversationController::class, 'messages'])->name('messages');
    Route::post('{uuid}/messages', [ConversationController::class, 'send'])->name('send');
});
