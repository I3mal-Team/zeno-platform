<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OtpInvalidException extends DomainException
{
    public function errorCode(): string
    {
        return 'OTP_INVALID';
    }

    protected function messageKey(): string
    {
        return 'otp_invalid';
    }

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
