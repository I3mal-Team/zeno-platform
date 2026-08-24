<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\Employer\SaveJobRequest;
use App\Services\CatalogService;
use App\Services\JobService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobController extends Controller
{
    public function __construct(private readonly JobService $jobs) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;

        return view('employer.jobs.index', [
            'jobs' => $this->jobs->listForEmployer($request->user(), $status, 12),
            'status' => $status,
        ]);
    }

    public function create(Request $request, CatalogService $catalog): View
    {
        return view('employer.jobs.create', $this->formData($catalog));
    }

    public function store(SaveJobRequest $request): RedirectResponse
    {
        $this->jobs->publish($request->user(), $request->toDto());

        return redirect()->route('employer.jobs.index')->with('status', 'تم نشر الإعلان.');
    }

    public function edit(Request $request, CatalogService $catalog, string $uuid): View
    {
        $job = $this->jobs->findForEmployer($request->user(), $uuid) ?? throw new NotFoundHttpException;

        return view('employer.jobs.edit', [...$this->formData($catalog), 'job' => $job]);
    }

    public function update(SaveJobRequest $request, string $uuid): RedirectResponse
    {
        $job = $this->jobs->findForEmployer($request->user(), $uuid) ?? throw new NotFoundHttpException;
        $this->jobs->update($request->user(), $job, $request->toDto());

        return redirect()->route('employer.jobs.index')->with('status', 'تم تحديث الإعلان.');
    }

    /** @return array<string, mixed> */
    private function formData(CatalogService $catalog): array
    {
        $options = $catalog->jobFormOptions();

        return [
            'categories' => $options->categories,
            'workTypes' => $options->workTypes,
            'salaryUnits' => $options->salaryUnits,
            'genderRequirements' => $options->genderRequirements,
            'nationalityRequirements' => $options->nationalityRequirements,
            'cities' => $catalog->activeCities(),
        ];
    }
}
