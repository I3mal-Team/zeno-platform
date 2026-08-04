<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Jobs;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Jobs\BrowseJobsRequest;
use App\Http\Resources\V1\Jobs\JobCardResource;
use App\Http\Resources\V1\Jobs\JobDetailResource;
use App\Services\JobService;
use App\Support\ViewerFingerprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobBrowseController extends ApiController
{
    public function __construct(private readonly JobService $jobs) {}

    public function index(BrowseJobsRequest $request): JsonResponse
    {
        $jobs = $this->jobs->browse($request->filters(), $request->perPage(), $request->user());

        return $this->successResponse(JobCardResource::collection($jobs));
    }

    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $jobs = $this->jobs->nearby(
            isset($validated['lat']) ? (float) $validated['lat'] : null,
            isset($validated['lng']) ? (float) $validated['lng'] : null,
            $request->user(),
            (int) ($validated['radius'] ?? 25),
            15,
        );

        return $this->successResponse(
            $jobs === null ? [] : JobCardResource::collection($jobs)
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $job = $this->jobs->findPublicBySlug($slug, $request->user()) ?? throw new NotFoundHttpException;

        $this->jobs->recordView($job, $request->user(), ViewerFingerprint::for($request));

        return $this->successResponse(new JobDetailResource($job));
    }
}
