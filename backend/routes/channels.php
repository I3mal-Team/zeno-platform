<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Support\Facades\Broadcast;

// Both surfaces authenticate a channel subscription here: the website over its
// session cookie (web guard) and the mobile app over its bearer token (sanctum).
// Naming both guards lets one channel definition serve either, since the
// broadcaster otherwise resolves the user with the web guard alone.
$guards = ['guards' => ['web', 'sanctum']];

// A user may listen only to their own notification stream.
Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return $user->id === $userId;
}, $guards);

// A chat thread is readable only by its two participants — the candidate and
// the hiring organization — reusing the exact rule that gates the REST thread.
Broadcast::channel('conversation.{uuid}', function (User $user, string $uuid) {
    return app(ConversationService::class)->participates($user, $uuid);
}, $guards);
