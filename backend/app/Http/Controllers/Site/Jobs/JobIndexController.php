<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Http\Requests\Site\JobSearchRequest;
use App\Services\CatalogService;
use App\Services\PublicSiteService;
use Illuminate\Contracts\View\View;

final class JobIndexController extends SiteController
{
    public function __invoke(
        JobSearchRequest $request,
        PublicSiteService $site,
        CatalogService $catalog,
    ): View {
        return view('site.pages.jobs', [
            'jobs' => $site->searchJobs($request->filters()),
            'categories' => $catalog->activeCategories(),
            'cities' => $catalog->activeCities(),
            'filters' => $request->filters(),
        ]);
    }
}
