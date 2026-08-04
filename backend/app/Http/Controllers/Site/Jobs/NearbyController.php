<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Services\JobService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class NearbyController extends SiteController
{
    public function __invoke(Request $request, JobService $jobs): View
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $hasCoords = isset($validated['lat'], $validated['lng']);

        $results = $jobs->nearby(
            $hasCoords ? (float) $validated['lat'] : null,
            $hasCoords ? (float) $validated['lng'] : null,
            $request->user(),
            25,
            12,
        );

        return view('site.pages.nearby', [
            'jobs' => $results,
            'byGps' => $hasCoords,
            'triedCoords' => $hasCoords,
        ]);
    }
}
