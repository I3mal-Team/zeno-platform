<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class ReportAlreadyFiledException extends DomainException
{
    public function errorCode(): string
    {
        return 'REPORT_ALREADY_FILED';
    }

    protected function messageKey(): string
    {
        return 'report_already_filed';
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
