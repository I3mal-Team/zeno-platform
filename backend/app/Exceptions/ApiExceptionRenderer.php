<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Domain\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * يحوّل أي استثناء إلى غلاف الخطأ الموحّد.
 *
 * كل كود يخرج من هنا يجب أن يقابله ثابت في
 * `mobile/lib/core/errors/error_codes.dart`.
 */
final class ApiExceptionRenderer
{
    public function render(Throwable $e, Request $request): JsonResponse
    {
        [$code, $status, $message, $details] = $this->map($e);

        $payload = [
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
                'details' => (object) $details,
                'trace_id' => $request->header('X-Request-Id'),
            ],
        ];

        // التفاصيل التشخيصية للمطوّر فقط — لا تتسرّب لعميل إنتاجي.
        if (config('app.debug') && $status >= 500) {
            $payload['error']['debug'] = [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile().':'.$e->getLine(),
            ];
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array{0: string, 1: int, 2: string, 3: array<string, mixed>}
     */
    private function map(Throwable $e): array
    {
        return match (true) {
            $e instanceof DomainException => [
                $e->errorCode(), $e->statusCode(), $e->getMessage(), $e->details(),
            ],

            $e instanceof ValidationException => [
                'VALIDATION_FAILED', 422, __('errors.validation_failed'), $e->errors(),
            ],

            $e instanceof AuthenticationException => [
                'SESSION_EXPIRED', 401, __('errors.session_expired'), [],
            ],

            $e instanceof AuthorizationException => [
                'FORBIDDEN', 403, __('errors.forbidden'), [],
            ],

            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => [
                'NOT_FOUND', 404, __('errors.not_found'), [],
            ],

            $e instanceof TooManyRequestsHttpException => [
                'RATE_LIMITED', 429, __('errors.rate_limited'), [],
            ],

            $e instanceof HttpExceptionInterface => [
                'UNKNOWN_ERROR', $e->getStatusCode(), __('errors.unknown'), [],
            ],

            default => ['UNKNOWN_ERROR', 500, __('errors.unknown'), []],
        };
    }
}
