<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Repositories\DeviceTokenRepository;
use App\Support\Fcm\FcmSender;
use Illuminate\Notifications\Notification;

/**
 * Delivers a notification to a user's registered devices via FCM. The
 * notification supplies the payload through a `toFcm()` method.
 */
final class FcmChannel
{
    public function __construct(
        private readonly FcmSender $sender,
        private readonly DeviceTokenRepository $tokens,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm') || ! isset($notifiable->id)) {
            return;
        }

        /** @var array{title: string, body: string, data?: array<string, mixed>} $payload */
        $payload = $notification->toFcm($notifiable);

        $this->sender->send(
            $this->tokens->tokensForUser((int) $notifiable->id),
            $payload['title'],
            $payload['body'],
            $payload['data'] ?? [],
        );
    }
}
