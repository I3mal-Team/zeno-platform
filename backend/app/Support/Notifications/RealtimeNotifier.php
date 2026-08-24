<?php

declare(strict_types=1);

namespace App\Support\Notifications;

use App\Events\NotificationBroadcast;
use Throwable;

/**
 * Pushes a live notification over Reverb, tolerating an unavailable server: the
 * stored database notification is the source of truth, so a failed broadcast is
 * reported and swallowed rather than breaking the request that triggered it.
 */
final class RealtimeNotifier
{
    /** @param  array<string, mixed>  $payload */
    public static function push(int $userId, array $payload): void
    {
        try {
            NotificationBroadcast::dispatch($userId, $payload);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
