<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Marketing;

use App\Http\Controllers\Site\SiteController;
use App\Services\CatalogService;
use Illuminate\Contracts\View\View;

final class HomeController extends SiteController
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function __invoke(): View
    {
        return view('site.pages.home', [
            'categories' => $this->catalog->activeCategories(),
        ]);
    }
}
