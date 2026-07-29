<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class VerificationAlreadyPendingException extends DomainException
{
    public function errorCode(): string
    {
        return 'VERIFICATION_ALREADY_PENDING';
    }

    protected function messageKey(): string
    {
        return 'verification_already_pending';
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
