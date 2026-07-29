<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Services\PublicSiteService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CompanyShowController extends SiteController
{
    public function __invoke(string $slug, PublicSiteService $site): View
    {
        $company = $site->findCompany($slug) ?? throw new NotFoundHttpException;

        return view('site.pages.company', $company);
    }
}
