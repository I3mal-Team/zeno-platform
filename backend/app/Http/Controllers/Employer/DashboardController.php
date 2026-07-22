<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(private readonly OrganizationService $organizations) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $organization = $this->organizations->find($request->user());

        if ($organization === null) {
            return redirect()->route('employer.register');
        }

        return view('employer.dashboard', [
            'organization' => $organization,
            'stats' => [
                ['label' => 'وظائف نشطة', 'value' => 0, 'icon' => 'briefcase', 'fg' => '#8A6D12', 'bg' => '#FDF3D6'],
                ['label' => 'طلبات جديدة', 'value' => 0, 'icon' => 'task-list', 'fg' => '#1F8A4D', 'bg' => '#E7F4EC'],
                ['label' => 'إجمالي المشاهدات', 'value' => 0, 'icon' => 'eye', 'fg' => '#2E6E8A', 'bg' => '#E2EEF4'],
                ['label' => 'معدل القبول', 'value' => '—', 'icon' => 'chart-square', 'fg' => '#6A4E8A', 'bg' => '#ECE6F4'],
            ],
        ]);
    }
}
