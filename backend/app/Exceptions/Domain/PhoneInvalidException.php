<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class PhoneInvalidException extends DomainException
{
    public function errorCode(): string
    {
        return 'PHONE_INVALID';
    }

    protected function messageKey(): string
    {
        return 'phone_invalid';
    }

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
