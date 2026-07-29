<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use App\Repositories\ConversationRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ConversationService
{
    public function __construct(
        private readonly ConversationRepository $conversations,
        private readonly OrganizationRepository $organizations,
    ) {}

    /**
     * Opens the thread the moment an application is accepted (D-19). Idempotent:
     * the one-per-application unique key means a re-accept never duplicates it.
     */
    public function createForApplication(Application $application): Conversation
    {
        return $this->conversations->findForApplication($application->id)
            ?? $this->conversations->create([
                'application_id' => $application->id,
                'candidate_id' => $application->candidate_id,
                'organization_id' => $application->organization_id,
                'status' => 'active',
            ]);
    }

    /** @return Collection<int, Conversation> */
    public function listFor(User $user): Collection
    {
        $organization = $this->organizations->findForUser($user->id);

        return $organization !== null
            ? $this->conversations->forOrganization($organization->id)
            : $this->conversations->forCandidate($user->id);
    }

    /**
     * @return array{conversation: Conversation, messages: Collection<int, Message>}
     */
    public function messages(User $user, string $uuid): array
    {
        $conversation = $this->authorize($user, $uuid);

        // Opening the thread is the read receipt — there is no separate action
        // for it on either surface.
        $this->conversations->markRead($conversation->id, $user->id);

        return [
            'conversation' => $conversation,
            'messages' => $this->conversations->messagesFor($conversation->id),
        ];
    }

    /**
     * The thread list with each row's unread total attached, resolved in one
     * query rather than one per row.
     *
     * @return Collection<int, Conversation>
     */
    public function listWithUnreadFor(User $user): Collection
    {
        $conversations = $this->listFor($user);
        $counts = $this->conversations->unreadCountsFor($conversations->pluck('id')->all(), $user->id);

        return $conversations->each(
            fn (Conversation $conversation) => $conversation->setAttribute('unread_count', $counts[$conversation->id] ?? 0),
        );
    }

    /** Threads holding something this user has not opened — the nav badge. */
    public function unreadThreadCount(User $user): int
    {
        $organization = $this->organizations->findForUser($user->id);

        return $organization !== null
            ? $this->conversations->countUnreadThreadsForOrganization($organization->id, $user->id)
            : $this->conversations->countUnreadThreadsForCandidate($user->id);
    }

    public function send(User $user, string $uuid, string $body, string $clientUuid): Message
    {
        $conversation = $this->authorize($user, $uuid);

        $message = DB::transaction(function () use ($conversation, $user, $body, $clientUuid) {
            $message = $this->conversations->addMessage($conversation, $user->id, $body, $clientUuid);

            // Only a genuinely new message advances the thread and notifies —
            // an idempotent retry returns the stored message untouched.
            if ($message->wasRecentlyCreated) {
                $this->conversations->touchLastMessage($conversation);
                $conversation->counterpartOf($user)?->notify(
                    new MessageReceivedNotification($conversation, $message),
                );
            }

            return $message;
        });

        // Push the live copy to the thread's channel once the row is committed,
        // and only for a genuinely new message. A Reverb outage must never fail
        // a send that is already stored, so the dispatch is best-effort.
        if ($message->wasRecentlyCreated) {
            try {
                broadcast(new MessageSent($conversation, $message, $user->uuid));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $message;
    }

    /** Whether the user is a participant — the gate for both REST and the channel. */
    public function participates(User $user, string $uuid): bool
    {
        $conversation = $this->conversations->findByUuid($uuid);

        return $conversation !== null && $this->isParticipant($user, $conversation);
    }

    /** Resolves the thread only for a participant; others get a 404, not a 403. */
    private function authorize(User $user, string $uuid): Conversation
    {
        $conversation = $this->conversations->findByUuid($uuid) ?? throw new NotFoundHttpException;

        if (! $this->isParticipant($user, $conversation)) {
            throw new NotFoundHttpException;
        }

        return $conversation;
    }

    private function isParticipant(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->candidate_id
            || $this->organizations->findForUser($user->id)?->id === $conversation->organization_id;
    }
}
