<?php

declare(strict_types=1);

namespace App\View\Components\Employer;

use App\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

final class Sidebar extends Component
{
    public function __construct(public string $active = 'overview') {}

    /** @return list<array{key: string, label: string, icon: string, url: string, badge: int}> */
    private function items(): array
    {
        return [
            ['key' => 'overview', 'label' => 'نظرة عامة', 'icon' => 'chart-square', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'jobs', 'label' => 'وظائفي', 'icon' => 'briefcase', 'url' => route('employer.jobs.index'), 'badge' => 0],
            ['key' => 'applicants', 'label' => 'المتقدّمون', 'icon' => 'task-list', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'messages', 'label' => 'الرسائل', 'icon' => 'messages-2', 'url' => route('employer.dashboard'), 'badge' => 0],
        ];
    }

    public function render(): View
    {
        $organization = app(OrganizationService::class)->find(Auth::user());
        $name = $organization !== null ? $organization->name : 'منشأتك';
        $verified = $organization !== null && $organization->isVerified();

        return view('components.employer.sidebar', [
            'items' => $this->items(),
            'organizationName' => $name,
            'verificationLabel' => $verified ? 'منشأة موثّقة' : 'غير موثّقة',
            'initial' => mb_substr($name, 0, 1),
        ]);
    }
}
