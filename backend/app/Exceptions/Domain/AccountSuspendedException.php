<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class AccountSuspendedException extends DomainException
{
    public function errorCode(): string
    {
        return 'ACCOUNT_SUSPENDED';
    }

    protected function messageKey(): string
    {
        return 'account_suspended';
    }

    public function statusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
