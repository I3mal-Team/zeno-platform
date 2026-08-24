<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WhatsAppDirection;
use App\Exceptions\Domain\ConversationNotAllowedException;
use App\Models\Conversation;
use App\Models\User;
use App\Models\WhatsAppHandoff;
use App\Repositories\ConversationRepository;
use App\Repositories\OrganizationRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Hands a conversation over to WhatsApp. The row is written *before* the link
 * is returned: once both sides are on WhatsApp the platform sees nothing more,
 * so this is the only record that the handoff happened at all.
 */
final class WhatsAppHandoffService
{
    public function __construct(
        private readonly ConversationRepository $conversations,
        private readonly OrganizationRepository $organizations,
    ) {}

    /**
     * @return array{url: string, phone: string, message: string}
     */
    public function open(User $user, string $conversationUuid): array
    {
        $conversation = $this->conversations->findByUuid($conversationUuid) ?? throw new NotFoundHttpException;
        $application = $conversation->application;

        if (! $this->participates($user, $conversation)) {
            throw new NotFoundHttpException;
        }

        // The listing's contact channel is the candidate's agreement about how
        // they may be reached; WhatsApp is not available where it says app-only.
        if (! $application->contact_channel->allowsWhatsApp()) {
            throw new ConversationNotAllowedException;
        }

        $isCandidate = $user->id === $conversation->candidate_id;
        $counterpart = $conversation->counterpartOf($user) ?? throw new NotFoundHttpException;

        WhatsAppHandoff::query()->create([
            'application_id' => $application->id,
            'initiated_by_user_id' => $user->id,
            'direction' => $isCandidate
                ? WhatsAppDirection::CandidateToEmployer->value
                : WhatsAppDirection::EmployerToCandidate->value,
            'consented_at' => now(),
            'created_at' => now(),
        ]);

        $phone = ltrim($counterpart->phone_e164, '+');
        $message = $this->template($conversation, $isCandidate);

        return [
            'url' => 'https://wa.me/'.$phone.'?text='.rawurlencode($message),
            'phone' => $counterpart->phone_e164,
            'message' => $message,
        ];
    }

    /** The prefilled text, written from whichever side is opening it. */
    private function template(Conversation $conversation, bool $isCandidate): string
    {
        $application = $conversation->application;
        $jobTitle = $application->job->title;
        $reference = $application->reference_number;

        if ($isCandidate) {
            // The prototype pasted a public profile URL here. That link would
            // expose a candidate's details to anyone it was forwarded to, so
            // the reference number travels instead — the employer opens the
            // profile from their own dashboard, where ownership is checked.
            return implode("\n", [
                'السلام عليكم،',
                'أنا متقدّم على وظيفة: '.$jobTitle,
                'رقم الطلب: '.$reference,
                'يمكنكم الاطلاع على ملفي من لوحة المتقدّمين.',
                'شكراً لكم.',
            ]);
        }

        return implode("\n", [
            'السلام عليكم،',
            'معك '.$conversation->organization->name.' بخصوص تقديمك على وظيفة: '.$jobTitle,
            'رقم الطلب: '.$reference,
            'نودّ التواصل معك لإكمال الإجراءات.',
        ]);
    }

    private function participates(User $user, Conversation $conversation): bool
    {
        if ($user->id === $conversation->candidate_id) {
            return true;
        }

        return $this->organizations->findForUser($user->id)?->id === $conversation->organization_id;
    }
}
