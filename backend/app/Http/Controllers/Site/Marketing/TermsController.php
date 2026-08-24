<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\TermsViewModel;
use Illuminate\Contracts\View\View;

final class TermsController extends SiteController
{
    public function __invoke(TermsViewModel $viewModel): View
    {
        return view('site.pages.terms', [
            'lastUpdated' => $viewModel->lastUpdated(),
            'sections' => $viewModel->sections(),
        ]);
    }
}
