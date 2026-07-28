<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class ConversationRepository
{
    private const LIST_RELATIONS = [
        'candidate.candidateProfile', 'organization', 'application.job',
        'latestMessage',
    ];

    /** @return Collection<int, Conversation> */
    public function forCandidate(int $candidateId): Collection
    {
        return Conversation::query()
            ->with(self::LIST_RELATIONS)
            ->where('candidate_id', $candidateId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }

    /** @return Collection<int, Conversation> */
    public function forOrganization(int $organizationId): Collection
    {
        return Conversation::query()
            ->with(self::LIST_RELATIONS)
            ->where('organization_id', $organizationId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Threads whose last word came from the candidate — the closest thing to
     * "unread" the schema supports, since messages carry no read state.
     */
    public function countAwaitingReplyForOrganization(int $organizationId): int
    {
        return Conversation::query()
            ->where('organization_id', $organizationId)
            ->whereHas('latestMessage', fn ($q) => $q->whereColumn('messages.sender_id', 'conversations.candidate_id'))
            ->count();
    }

    public function findByUuid(string $uuid): ?Conversation
    {
        return Conversation::query()
            ->with(['candidate.candidateProfile', 'organization', 'application.job'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function findForApplication(int $applicationId): ?Conversation
    {
        return Conversation::query()->where('application_id', $applicationId)->first();
    }

    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): Conversation
    {
        return Conversation::query()->create([
            'uuid' => (string) Str::uuid(),
            ...$attributes,
        ]);
    }

    /** @return Collection<int, Message> */
    public function messagesFor(int $conversationId): Collection
    {
        return Message::query()
            ->with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Idempotent on the sender's client_uuid so a retried send never duplicates.
     */
    public function addMessage(
        Conversation $conversation,
        int $senderId,
        string $body,
        string $clientUuid,
    ): Message {
        return Message::query()->firstOrCreate(
            ['conversation_id' => $conversation->id, 'client_uuid' => $clientUuid],
            [
                'uuid' => (string) Str::uuid(),
                'sender_id' => $senderId,
                'type' => 'text',
                'body' => $body,
            ],
        );
    }

    public function touchLastMessage(Conversation $conversation): void
    {
        $conversation->forceFill(['last_message_at' => now()])->save();
    }
}
