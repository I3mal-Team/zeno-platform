<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Services\JobService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobStatusController extends Controller
{
    public function __construct(private readonly JobService $jobs) {}

    public function pause(Request $request, string $uuid): RedirectResponse
    {
        $this->jobs->pause($request->user(), $this->resolve($request->user(), $uuid));

        return back()->with('status', 'تم إيقاف الإعلان.');
    }

    public function resume(Request $request, string $uuid): RedirectResponse
    {
        $this->jobs->resume($request->user(), $this->resolve($request->user(), $uuid));

        return back()->with('status', 'تم استئناف الإعلان.');
    }

    public function close(Request $request, string $uuid): RedirectResponse
    {
        $this->jobs->close($request->user(), $this->resolve($request->user(), $uuid));

        return back()->with('status', 'تم إغلاق الإعلان.');
    }

    private function resolve(User $user, string $uuid): Job
    {
        return $this->jobs->findForEmployer($user, $uuid) ?? throw new NotFoundHttpException;
    }
}
