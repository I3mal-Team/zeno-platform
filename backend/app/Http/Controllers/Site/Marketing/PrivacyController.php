<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\PrivacyViewModel;
use Illuminate\Contracts\View\View;

final class PrivacyController extends SiteController
{
    public function __invoke(PrivacyViewModel $viewModel): View
    {
        return view('site.pages.legal', [
            'pageTitle' => 'سياسة الخصوصية',
            'intro' => $viewModel->intro(),
            'lastUpdated' => $viewModel->lastUpdated(),
            'sections' => $viewModel->sections(),
            'ctaTitle' => 'لديك سؤال عن بياناتك؟',
            'ctaSubtitle' => 'راسل فريق الدعم وسنجيبك.',
            'contactEmail' => 'hello@zeno.sa',
        ]);
    }
}
