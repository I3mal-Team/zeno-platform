<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employer;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Jobs\JobDetailResource;
use App\Models\Job;
use App\Models\User;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobStatusController extends ApiController
{
    public function __construct(private readonly JobService $jobs) {}

    public function pause(Request $request, string $uuid): JsonResponse
    {
        return $this->respond($this->jobs->pause($request->user(), $this->resolve($request->user(), $uuid)));
    }

    public function resume(Request $request, string $uuid): JsonResponse
    {
        return $this->respond($this->jobs->resume($request->user(), $this->resolve($request->user(), $uuid)));
    }

    public function fill(Request $request, string $uuid): JsonResponse
    {
        return $this->respond($this->jobs->markFilled($request->user(), $this->resolve($request->user(), $uuid)));
    }

    public function close(Request $request, string $uuid): JsonResponse
    {
        return $this->respond($this->jobs->close($request->user(), $this->resolve($request->user(), $uuid)));
    }

    private function resolve(User $user, string $uuid): Job
    {
        return $this->jobs->findForEmployer($user, $uuid) ?? throw new NotFoundHttpException;
    }

    private function respond(Job $job): JsonResponse
    {
        return $this->successResponse(new JobDetailResource($job->fresh()), __('messages.job_status_updated'));
    }
}
