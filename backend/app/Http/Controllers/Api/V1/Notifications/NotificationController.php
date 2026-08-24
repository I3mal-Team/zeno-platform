<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends ApiController
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): JsonResponse
    {
        $page = $this->notifications->list($request->user(), 20);

        return $this->successResponse(NotificationResource::collection($page));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->successResponse(['unread' => $this->notifications->unreadCount($request->user())]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->notifications->markRead($request->user(), $id);

        return $this->noContentResponse();
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notifications->markAllRead($request->user());

        return $this->noContentResponse();
    }
}
