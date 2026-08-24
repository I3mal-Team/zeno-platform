<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\AccountDeletionViewModel;
use Illuminate\Contracts\View\View;

/**
 * Google Play requires this page to be reachable without signing in, so it lives
 * at a top-level path rather than under any auth-gated prefix. See the route
 * test that pins this.
 */
final class AccountDeletionController extends SiteController
{
    public function __invoke(AccountDeletionViewModel $viewModel): View
    {
        return view('site.pages.legal', [
            'pageTitle' => 'حذف الحساب',
            'intro' => $viewModel->intro(),
            'lastUpdated' => $viewModel->lastUpdated(),
            'sections' => $viewModel->sections(),
            'ctaTitle' => 'جاهز لتقديم الطلب؟',
            'ctaSubtitle' => 'أرسل طلبك لبريد الدعم وسنتواصل معك للتحقّق.',
            'contactEmail' => 'hello@zeno.sa',
        ]);
    }
}
