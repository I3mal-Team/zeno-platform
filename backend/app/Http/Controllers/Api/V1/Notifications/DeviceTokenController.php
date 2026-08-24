<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Notifications\RegisterDeviceRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeviceTokenController extends ApiController
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function store(RegisterDeviceRequest $request): JsonResponse
    {
        $this->notifications->registerDevice(
            $request->user(),
            $request->string('token')->toString(),
            $request->string('platform')->toString(),
        );

        return $this->noContentResponse();
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->notifications->forgetDevice($request->string('token')->toString());

        return $this->noContentResponse();
    }
}
