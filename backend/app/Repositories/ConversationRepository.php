<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
     * Marks everything the user has not sent themselves as read, up to now.
     * The composite primary key makes a repeat call a no-op, so opening a
     * thread twice costs one insert.
     *
     * @return int rows newly marked
     */
    public function markRead(int $conversationId, int $userId): int
    {
        $unread = Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('message_reads')
                ->whereColumn('message_reads.message_id', 'messages.id')
                ->where('message_reads.user_id', $userId))
            ->pluck('id');

        if ($unread->isEmpty()) {
            return 0;
        }

        return DB::table('message_reads')->insertOrIgnore(
            $unread->map(fn (int $id) => [
                'message_id' => $id,
                'user_id' => $userId,
                'read_at' => now(),
            ])->all(),
        );
    }

    /** Messages addressed to [$userId] in [$conversationId] that they have not opened. */
    public function unreadCountFor(int $conversationId, int $userId): int
    {
        return $this->unreadMessages($userId)->where('conversation_id', $conversationId)->count();
    }

    /**
     * Unread totals per conversation for one user, keyed by conversation id —
     * one query for a whole list rather than one per row.
     *
     * @param  array<int, int>  $conversationIds
     * @return array<int, int>
     */
    public function unreadCountsFor(array $conversationIds, int $userId): array
    {
        if ($conversationIds === []) {
            return [];
        }

        return $this->unreadMessages($userId)
            ->whereIn('conversation_id', $conversationIds)
            ->selectRaw('conversation_id, COUNT(*) AS total')
            ->groupBy('conversation_id')
            ->pluck('total', 'conversation_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /** Threads in this organization holding at least one message nobody here has opened. */
    public function countUnreadThreadsForOrganization(int $organizationId, int $userId): int
    {
        return $this->unreadMessages($userId)
            ->whereIn('conversation_id', Conversation::query()->where('organization_id', $organizationId)->select('id'))
            ->distinct()
            ->count('conversation_id');
    }

    /** Threads belonging to this candidate holding at least one unopened message. */
    public function countUnreadThreadsForCandidate(int $candidateId): int
    {
        return $this->unreadMessages($candidateId)
            ->whereIn('conversation_id', Conversation::query()->where('candidate_id', $candidateId)->select('id'))
            ->distinct()
            ->count('conversation_id');
    }

    /** @return Builder<Message> */
    private function unreadMessages(int $userId): Builder
    {
        return Message::query()
            ->where('sender_id', '!=', $userId)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('message_reads')
                ->whereColumn('message_reads.message_id', 'messages.id')
                ->where('message_reads.user_id', $userId));
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
