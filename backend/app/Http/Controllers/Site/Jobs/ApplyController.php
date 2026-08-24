<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site\Jobs;

use App\Http\Controllers\Site\SiteController;
use App\Http\Requests\Jobs\StoreApplicationRequest;
use App\Services\ApplicationService;
use App\Services\JobService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** Reaching this requires a signed-in candidate (auth + role:candidate). */
final class ApplyController extends SiteController
{
    public function __invoke(StoreApplicationRequest $request, JobService $jobs, ApplicationService $applications, string $slug): RedirectResponse
    {
        $job = $jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $application = $applications->apply(
            $request->user(),
            $job,
            $request->scalarAnswers(),
            $request->uploadedFiles(),
        );

        return redirect()
            ->route('site.jobs.show', $slug)
            ->with('status', 'تم إرسال طلبك بنجاح — رقم الطلب '.$application->reference_number);
    }
}
