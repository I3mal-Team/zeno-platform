<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Applications;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Jobs\StoreApplicationRequest;
use App\Http\Resources\V1\Applications\ApplicationResource;
use App\Services\ApplicationService;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApplicationController extends ApiController
{
    public function __construct(private readonly ApplicationService $applications) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            ApplicationResource::collection($this->applications->listForCandidate($request->user()))
        );
    }

    public function store(StoreApplicationRequest $request, JobService $jobs, string $slug): JsonResponse
    {
        $job = $jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $application = $this->applications->apply(
            $request->user(),
            $job,
            $request->scalarAnswers(),
            $request->uploadedFiles(),
        );

        return $this->createdResponse(
            new ApplicationResource($application->load('job.organization', 'job.category')),
            __('messages.application_submitted'),
        );
    }

    public function withdraw(Request $request, string $reference): JsonResponse
    {
        $this->applications->withdraw($request->user(), $reference);

        return $this->successResponse(null, __('messages.application_withdrawn'));
    }
}
