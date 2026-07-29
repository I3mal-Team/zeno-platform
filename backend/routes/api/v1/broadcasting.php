<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Token-authenticated broadcasting auth for the mobile app. The framework's
// default /broadcasting/auth rides the web session guard and CSRF, which a
// bearer-token client cannot satisfy; this mirror runs on the stateless API
// stack so the same channel rules in routes/channels.php gate a Sanctum user.
Route::post('broadcasting/auth', fn (Request $request) => Broadcast::auth($request))
    ->middleware('auth:sanctum')
    ->name('broadcasting.auth');
