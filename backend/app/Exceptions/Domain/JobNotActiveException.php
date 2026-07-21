<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class JobNotActiveException extends DomainException
{
    public function errorCode(): string
    {
        return 'JOB_NOT_ACTIVE';
    }

    protected function messageKey(): string
    {
        return 'job_not_active';
    }

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
