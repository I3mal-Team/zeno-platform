<?php

declare(strict_types=1);

namespace App\Services;

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

        return [
            'conversation' => $conversation,
            'messages' => $this->conversations->messagesFor($conversation->id),
        ];
    }

    public function send(User $user, string $uuid, string $body, string $clientUuid): Message
    {
        $conversation = $this->authorize($user, $uuid);

        return DB::transaction(function () use ($conversation, $user, $body, $clientUuid) {
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
    }

    /** Resolves the thread only for a participant; others get a 404, not a 403. */
    private function authorize(User $user, string $uuid): Conversation
    {
        $conversation = $this->conversations->findByUuid($uuid) ?? throw new NotFoundHttpException;

        $isParticipant = $user->id === $conversation->candidate_id
            || $this->organizations->findForUser($user->id)?->id === $conversation->organization_id;

        if (! $isParticipant) {
            throw new NotFoundHttpException;
        }

        return $conversation;
    }
}
