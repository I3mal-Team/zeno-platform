<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OrganizationMissingException extends DomainException
{
    public function errorCode(): string
    {
        return 'ORGANIZATION_MISSING';
    }

    protected function messageKey(): string
    {
        return 'organization_missing';
    }

    public function statusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
