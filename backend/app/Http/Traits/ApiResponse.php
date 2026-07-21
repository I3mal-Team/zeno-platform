<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * The single response envelope for the API. Clients unwrap this shape once,
 * centrally, so any deviation breaks every screen at the same time.
 */
trait ApiResponse
{
    protected function successResponse(
        mixed $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $this->unwrap($data),
        ];

        if ($meta = $this->extractMeta($data)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function createdResponse(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    protected function noContentResponse(?string $message = null): JsonResponse
    {
        return $this->successResponse(null, $message, Response::HTTP_OK);
    }

    /** @param  array<string, mixed>  $details */
    protected function errorResponse(
        string $code,
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $details = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
                'details' => (object) $details,
                'trace_id' => request()->header('X-Request-Id'),
            ],
        ], $status);
    }

    /** @return array<string, mixed>|null */
    private function extractMeta(mixed $data): ?array
    {
        $paginator = match (true) {
            $data instanceof ResourceCollection => $data->resource,
            $data instanceof Paginator => $data,
            default => null,
        };

        if (! $paginator instanceof Paginator) {
            return null;
        }

        // simplePaginate cannot know the total, so these stay null rather than
        // disappearing: clients rely on the shape being constant.
        $countable = $paginator instanceof LengthAwarePaginator;

        return [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $countable ? $paginator->total() : null,
                'last_page' => $countable ? $paginator->lastPage() : null,
                'has_more' => $paginator->hasMorePages(),
            ],
        ];
    }

    private function unwrap(mixed $data): mixed
    {
        return match (true) {
            $data instanceof ResourceCollection => $data->resolve(),
            $data instanceof JsonResource => $data->resolve(),
            default => $data,
        };
    }
}
