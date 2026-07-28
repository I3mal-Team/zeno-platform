<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\EmployerOverviewService;
use App\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly OrganizationService $organizations,
        private readonly EmployerOverviewService $overview,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        $organization = $this->organizations->find($request->user());

        if ($organization === null) {
            return redirect()->route('employer.register');
        }

        return view('employer.dashboard', [
            'organization' => $organization,
            ...$this->overview->for($organization),
        ]);
    }
}
