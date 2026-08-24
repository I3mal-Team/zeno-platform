<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Services\JobService;
use App\Services\SavedJobService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SavedJobController extends SiteController
{
    public function __construct(
        private readonly SavedJobService $saved,
        private readonly JobService $jobs,
    ) {}

    public function index(Request $request): View
    {
        return view('site.saved', [
            'jobs' => $this->saved->listFor($request->user(), 12),
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $job = $this->jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $this->saved->save($request->user(), $job);

        return back();
    }

    public function destroy(Request $request, string $slug): RedirectResponse
    {
        $job = $this->jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $this->saved->remove($request->user(), $job);

        return back();
    }
}
