<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Profile\SaveCandidateProfileRequest;
use App\Http\Resources\V1\Profile\CandidateProfileResource;
use App\Services\CandidateProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CandidateProfileController extends ApiController
{
    public function __construct(private readonly CandidateProfileService $profiles) {}

    public function show(Request $request): JsonResponse
    {
        $profile = $this->profiles->find($request->user())
            ?? throw new NotFoundHttpException;

        return $this->successResponse(new CandidateProfileResource($profile));
    }

    public function save(SaveCandidateProfileRequest $request): JsonResponse
    {
        $profile = $this->profiles->save($request->user(), $request->toDto());

        return $this->successResponse(
            new CandidateProfileResource($profile->load('city')),
            __('messages.profile_saved'),
        );
    }
}
