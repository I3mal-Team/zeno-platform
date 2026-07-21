<?php

declare(strict_types=1);

namespace App\View\Components\Employer;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Sidebar extends Component
{
    public function __construct(public string $active = 'overview') {}

    /** @return list<array{key: string, label: string, icon: string, url: string, badge: int}> */
    private function items(): array
    {
        return [
            ['key' => 'overview', 'label' => 'نظرة عامة', 'icon' => 'chart-square', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'jobs', 'label' => 'وظائفي', 'icon' => 'briefcase', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'applicants', 'label' => 'المتقدّمون', 'icon' => 'task-list', 'url' => route('employer.dashboard'), 'badge' => 0],
            ['key' => 'messages', 'label' => 'الرسائل', 'icon' => 'messages-2', 'url' => route('employer.dashboard'), 'badge' => 0],
        ];
    }

    public function render(): View
    {
        return view('components.employer.sidebar', [
            'items' => $this->items(),
            'organizationName' => 'منشأتك',
            'verificationLabel' => 'غير موثّقة',
            'initial' => 'م',
        ]);
    }
}
