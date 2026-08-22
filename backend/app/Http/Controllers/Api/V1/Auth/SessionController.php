<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Auth\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class SessionController extends ApiController
{
    public function __construct(private readonly AuthService $auth) {}

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var PersonalAccessToken $token */
        $token = $user->currentAccessToken();

        $this->auth->logout($user, $token->getKey());

        return $this->noContentResponse(__('messages.signed_out'));
    }

    public function logoutEverywhere(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->auth->logoutEverywhere($user);

        return $this->noContentResponse(__('messages.signed_out_everywhere'));
    }
}
