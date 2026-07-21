<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OtpExpiredException extends DomainException
{
    public function errorCode(): string
    {
        return 'OTP_EXPIRED';
    }

    protected function messageKey(): string
    {
        return 'otp_expired';
    }

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
