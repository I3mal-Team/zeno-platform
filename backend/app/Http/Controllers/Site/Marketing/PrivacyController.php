<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\PrivacyViewModel;
use Illuminate\Contracts\View\View;

/**
 * Its own page rather than a section of the terms, because both stores ask for
 * a URL that is the privacy policy and nothing else.
 */
final class PrivacyController extends SiteController
{
    public function __invoke(PrivacyViewModel $viewModel): View
    {
        return view('site.pages.legal', [
            'heading' => 'سياسة الخصوصية',
            'lastUpdated' => $viewModel->lastUpdated(),
            'sections' => $viewModel->sections(),
        ]);
    }
}
