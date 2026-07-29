<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Messaging;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Messaging\SendMessageRequest;
use App\Http\Resources\V1\Messaging\ConversationResource;
use App\Http\Resources\V1\Messaging\MessageResource;
use App\Services\ConversationService;
use App\Services\WhatsAppHandoffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ConversationController extends ApiController
{
    public function __construct(private readonly ConversationService $conversations) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            ConversationResource::collection($this->conversations->listWithUnreadFor($request->user())),
        );
    }

    public function messages(Request $request, string $uuid): JsonResponse
    {
        ['messages' => $messages] = $this->conversations->messages($request->user(), $uuid);

        return $this->successResponse(MessageResource::collection($messages));
    }

    /**
     * Logs the move to WhatsApp and returns the deep link with its prefilled
     * text. The record is written first, so the platform keeps a trace of a
     * conversation it is about to lose sight of.
     */
    public function whatsapp(Request $request, string $uuid, WhatsAppHandoffService $handoffs): JsonResponse
    {
        return $this->successResponse($handoffs->open($request->user(), $uuid));
    }

    public function send(SendMessageRequest $request, string $uuid): JsonResponse
    {
        $message = $this->conversations->send(
            $request->user(),
            $uuid,
            $request->body(),
            $request->clientUuid(),
        );

        return $this->successResponse(new MessageResource($message));
    }
}
