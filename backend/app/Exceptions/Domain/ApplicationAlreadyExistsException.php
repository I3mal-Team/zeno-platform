<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class ApplicationAlreadyExistsException extends DomainException
{
    public function errorCode(): string
    {
        return 'APPLICATION_ALREADY_EXISTS';
    }

    protected function messageKey(): string
    {
        return 'application_already_exists';
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
