<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Jobs;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Jobs\JobCardResource;
use App\Services\JobService;
use App\Services\SavedJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SavedJobController extends ApiController
{
    public function __construct(
        private readonly SavedJobService $saved,
        private readonly JobService $jobs,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            JobCardResource::collection($this->saved->listFor($request->user(), 15))
        );
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $job = $this->jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $this->saved->save($request->user(), $job);

        return $this->successResponse(null, __('messages.job_saved'));
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $job = $this->jobs->findPublicBySlug($slug) ?? throw new NotFoundHttpException;

        $this->saved->remove($request->user(), $job);

        return $this->successResponse(null, __('messages.job_unsaved'));
    }
}
