<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Services\JobService;
use App\Services\PublicSiteService;
use App\Support\ViewerFingerprint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobShowController extends SiteController
{
    public function __invoke(Request $request, string $slug, PublicSiteService $site, JobService $jobs): View
    {
        $job = $site->findJob($slug) ?? throw new NotFoundHttpException;

        $jobs->recordView($job, $request->user(), ViewerFingerprint::for($request));

        return view('site.pages.job', ['job' => $job]);
    }
}
