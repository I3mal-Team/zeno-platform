<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\Concerns\Pushable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Tells the other participant a new message landed in their thread. */
final class MessageReceivedNotification extends Notification
{
    use Pushable;

    public function __construct(
        private readonly Conversation $conversation,
        private readonly Message $message,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return $this->channelsWithPush();
    }

    /** @return array{title: string, body: string, data: array<string, mixed>} */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'رسالة جديدة',
            'body' => Str::limit((string) $this->message->body, 80),
            'data' => [
                'type' => 'message_received',
                'conversation_uuid' => $this->conversation->uuid,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'message_received',
            'conversation_uuid' => $this->conversation->uuid,
            'preview' => Str::limit((string) $this->message->body, 80),
        ];
    }
}
