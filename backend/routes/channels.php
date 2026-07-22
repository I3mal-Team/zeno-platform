<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// A user may listen only to their own notification stream.
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return $user->id === $userId;
});
