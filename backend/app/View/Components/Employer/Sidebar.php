<?php

declare(strict_types=1);

namespace App\View\Components\Employer;

use App\Enums\ApplicationStatus;
use App\Repositories\ApplicationRepository;
use App\Repositories\ConversationRepository;
use App\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

final class Sidebar extends Component
{
    public function __construct(public string $active = 'overview') {}

    /**
     * Badges count what is actually waiting on the employer: submissions they
     * have not opened, and threads with an unread message.
     *
     * @return list<array{key: string, label: string, icon: string, url: string, badge: int}>
     */
    private function items(?int $organizationId): array
    {
        $pending = $organizationId === null ? 0 : app(ApplicationRepository::class)
            ->countForOrganization($organizationId, [ApplicationStatus::Submitted->value]);

        $unread = $organizationId === null ? 0 : app(ConversationRepository::class)
            ->countUnreadThreadsForOrganization($organizationId, (int) Auth::id());

        return [
            ['key' => 'overview', 'label' => 'نظرة عامة', 'icon' => 'chart-square', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'jobs', 'label' => 'وظائفي', 'icon' => 'briefcase', 'url' => route('employer.jobs.index'), 'badge' => 0],
            ['key' => 'applicants', 'label' => 'المتقدّمون', 'icon' => 'user-1', 'url' => route('employer.applicants.index'), 'badge' => $pending],
            ['key' => 'messages', 'label' => 'الرسائل', 'icon' => 'messages-2', 'url' => route('employer.messages.index'), 'badge' => $unread],
        ];
    }

    public function render(): View
    {
        $organization = app(OrganizationService::class)->find(Auth::user());
        $name = $organization !== null ? $organization->name : 'منشأتك';
        $verified = $organization !== null && $organization->isVerified();

        return view('components.employer.sidebar', [
            'items' => $this->items($organization?->id),
            'organizationName' => $name,
            'verificationLabel' => $verified ? 'منشأة موثّقة' : 'غير موثّقة',
            'initial' => mb_substr($name, 0, 1),
        ]);
    }
}
