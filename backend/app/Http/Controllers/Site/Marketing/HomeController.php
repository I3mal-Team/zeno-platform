<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\HomeViewModel;
use App\Services\PublicSiteService;
use Illuminate\Contracts\View\View;

final class HomeController extends SiteController
{
    public function __invoke(PublicSiteService $site, HomeViewModel $viewModel): View
    {
        $latestJobs = $site->latestJobs();

        return view('site.pages.home', [
            'categories' => $site->categoriesWithCounts(),
            'latestJobs' => $latestJobs,
            'previewJobs' => $latestJobs->take(3),
            'stats' => $viewModel->stats(),
            'steps' => $viewModel->steps(),
            'features' => $viewModel->features(),
            'employerPoints' => $viewModel->employerPoints(),
            'faqs' => $viewModel->faqs(),
        ]);
    }
}
