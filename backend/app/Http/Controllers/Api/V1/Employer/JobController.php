<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employer;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Jobs\SaveJobRequest;
use App\Http\Resources\V1\Jobs\JobCardResource;
use App\Http\Resources\V1\Jobs\JobDetailResource;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobController extends ApiController
{
    public function __construct(private readonly JobService $jobs) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->string('status')->toString() ?: null;
        $perPage = (int) $request->integer('per_page', 15);

        $jobs = $this->jobs->listForEmployer($request->user(), $status, min($perPage, 50));

        return $this->successResponse(JobCardResource::collection($jobs));
    }

    public function store(SaveJobRequest $request): JsonResponse
    {
        $job = $this->jobs->publish($request->user(), $request->toDto());

        return $this->createdResponse(
            new JobDetailResource($job->load($this->detailRelations())),
            __('messages.job_published'),
        );
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $job = $this->jobs->findForEmployer($request->user(), $uuid) ?? throw new NotFoundHttpException;

        return $this->successResponse(new JobDetailResource($job));
    }

    public function update(SaveJobRequest $request, string $uuid): JsonResponse
    {
        $job = $this->jobs->findForEmployer($request->user(), $uuid) ?? throw new NotFoundHttpException;
        $updated = $this->jobs->update($request->user(), $job, $request->toDto());

        return $this->successResponse(
            new JobDetailResource($updated->load($this->detailRelations())),
            __('messages.job_updated'),
        );
    }

    /** @return list<string> */
    private function detailRelations(): array
    {
        return [
            'organization', 'category', 'workType', 'salaryUnit', 'city',
            'district', 'genderRequirement', 'nationalityRequirement',
        ];
    }
}
