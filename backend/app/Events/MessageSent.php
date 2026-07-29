<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * A newly posted chat message, pushed live to both sides of the thread over
 * Reverb. Broadcast-now (not queued) so the sender's request drives delivery
 * and a resilient caller can swallow a Reverb outage without failing the send.
 *
 * The payload never carries `is_mine` — that is per-viewer. It ships the
 * sender's public uuid instead, and each client derives ownership by comparing
 * it to their own, deduplicating by message uuid so a client that already holds
 * the message (its optimistic copy or the HTTP response) never shows it twice.
 */
final class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly Conversation $conversation,
        public readonly Message $message,
        public readonly string $senderUuid,
    ) {}

    /** @return list<Channel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.'.$this->conversation->uuid)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'uuid' => $this->message->uuid,
            'body' => $this->message->body,
            'type' => $this->message->type,
            'sender_id' => $this->senderUuid,
            'created_at' => $this->message->created_at->toIso8601String(),
            'conversation_uuid' => $this->conversation->uuid,
        ];
    }
}
