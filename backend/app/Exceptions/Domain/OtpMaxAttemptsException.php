<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OtpMaxAttemptsException extends DomainException
{
    public function errorCode(): string
    {
        return 'OTP_MAX_ATTEMPTS';
    }

    protected function messageKey(): string
    {
        return 'otp_max_attempts';
    }

    public function statusCode(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }
}
