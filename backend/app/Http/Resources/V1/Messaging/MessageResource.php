<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Messaging;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
final class MessageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'body' => $this->body,
            'type' => $this->type,
            // Resolved server-side so the client never has to match ids.
            'is_mine' => $request->user()?->getKey() === $this->sender_id,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
