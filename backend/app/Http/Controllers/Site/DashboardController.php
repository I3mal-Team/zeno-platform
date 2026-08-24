<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\ConversationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * The job seeker's home base on the public site: one overview of their
 * applications and conversations, the candidate mirror of the employer
 * dashboard.
 */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applications,
        private readonly ConversationService $conversations,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $applications = $this->applications->listForCandidate($user);
        $conversations = $this->conversations->listWithUnreadFor($user);

        $countOf = fn (ApplicationStatus ...$statuses): int => $applications
            ->filter(fn ($application): bool => in_array($application->status, $statuses, true))
            ->count();

        return view('site.dashboard', [
            'fullName' => $user->candidateProfile?->full_name,
            'completion' => (int) ($user->candidateProfile->completion_percentage ?? 0),
            'applications' => $applications,
            'conversations' => $conversations,
            'stats' => [
                [
                    'label' => 'إجمالي الطلبات', 'value' => $applications->count(),
                    'icon' => 'document-text-1', 'bg' => '#FDF1CC', 'fg' => '#8A6D12',
                ],
                [
                    'label' => 'قيد المراجعة',
                    'value' => $countOf(ApplicationStatus::Submitted, ApplicationStatus::Review),
                    'icon' => 'clock', 'bg' => '#E2EEF4', 'fg' => '#2E6E8A',
                ],
                [
                    'label' => 'مقبولة', 'value' => $countOf(ApplicationStatus::Accepted),
                    'icon' => 'tick-circle', 'bg' => '#E7F4EC', 'fg' => '#1F8A4D',
                ],
                [
                    'label' => 'المحادثات', 'value' => $conversations->count(),
                    'icon' => 'messages-2', 'bg' => '#ECE6F4', 'fg' => '#6A4E8A',
                ],
            ],
        ]);
    }
}
