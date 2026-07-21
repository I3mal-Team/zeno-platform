<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Services\PublicSiteService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobShowController extends SiteController
{
    public function __invoke(string $slug, PublicSiteService $site): View
    {
        $job = $site->findJob($slug) ?? throw new NotFoundHttpException;

        return view('site.pages.job', ['job' => $job]);
    }
}
