<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Profile\UploadAvatarRequest;
use App\Http\Resources\V1\Profile\CandidateProfileResource;
use App\Services\CandidateProfileService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AvatarController extends ApiController
{
    public function __construct(private readonly CandidateProfileService $profiles) {}

    public function store(UploadAvatarRequest $request): JsonResponse
    {
        $profile = $this->profiles->find($request->user())
            ?? throw new NotFoundHttpException;

        $this->profiles->saveAvatar($profile, $request->file('avatar'));

        return $this->successResponse(
            new CandidateProfileResource($profile->fresh(['city'])),
            __('messages.avatar_saved'),
        );
    }
}
