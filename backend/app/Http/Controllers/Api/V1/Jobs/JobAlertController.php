<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Jobs;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Jobs\SaveJobAlertRequest;
use App\Http\Resources\V1\Jobs\JobAlertResource;
use App\Services\JobAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class JobAlertController extends ApiController
{
    public function __construct(private readonly JobAlertService $alerts) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            JobAlertResource::collection($this->alerts->listFor($request->user()))
        );
    }

    public function store(SaveJobAlertRequest $request): JsonResponse
    {
        $alert = $this->alerts->create($request->user(), $request->attributesForAlert());

        return $this->createdResponse(
            new JobAlertResource($alert->load('category', 'city', 'workType')),
            __('messages.alert_created'),
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->alerts->delete($request->user(), $id);

        return $this->successResponse(null, __('messages.alert_deleted'));
    }
}
