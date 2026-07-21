<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\V1\Auth\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

final class OtpController extends ApiController
{
    public function __construct(private readonly AuthService $auth) {}

    public function request(RequestOtpRequest $request): JsonResponse
    {
        $expiresIn = $this->auth->requestOtp(
            $request->toDto(),
            $request->ip(),
            $request->userAgent(),
        );

        return $this->successResponse(
            ['expires_in_seconds' => $expiresIn],
            __('messages.otp_sent'),
        );
    }

    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->auth->verifyOtp($request->toDto(), $request->deviceName());

        return $this->successResponse([
            'token' => $result['token'],
            'is_new_user' => $result['isNewUser'],
            'user' => new UserResource($result['user']),
        ], __('messages.signed_in'));
    }
}
