<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Http\ViewModels\Site\PricingViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PricingController extends SiteController
{
    public function __invoke(Request $request, PricingViewModel $viewModel): View
    {
        $yearly = $request->query('cycle') === 'year';

        return view('site.pages.pricing', [
            'yearly' => $yearly,
            'plans' => $viewModel->plans($yearly),
            'comparison' => $viewModel->comparison(),
            'faqs' => $viewModel->faqs(),
        ]);
    }
}
