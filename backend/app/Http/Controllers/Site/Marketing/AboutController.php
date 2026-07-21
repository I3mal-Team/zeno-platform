<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\AboutViewModel;
use Illuminate\Contracts\View\View;

final class AboutController extends SiteController
{
    public function __invoke(AboutViewModel $viewModel): View
    {
        return view('site.pages.about', [
            'stats' => $viewModel->stats(),
            'values' => $viewModel->values(),
            'milestones' => $viewModel->milestones(),
            'team' => $viewModel->team(),
        ]);
    }
}
