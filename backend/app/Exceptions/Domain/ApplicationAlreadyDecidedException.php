<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class ApplicationAlreadyDecidedException extends DomainException
{
    public function errorCode(): string
    {
        return 'APPLICATION_ALREADY_DECIDED';
    }

    protected function messageKey(): string
    {
        return 'application_already_decided';
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
