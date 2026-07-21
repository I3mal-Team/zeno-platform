<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OrganizationNotVerifiedException extends DomainException
{
    public function errorCode(): string
    {
        return 'ORGANIZATION_NOT_VERIFIED';
    }

    protected function messageKey(): string
    {
        return 'organization_not_verified';
    }

    public function statusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
