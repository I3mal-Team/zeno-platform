<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class VacanciesExhaustedException extends DomainException
{
    public function errorCode(): string
    {
        return 'VACANCIES_EXHAUSTED';
    }

    protected function messageKey(): string
    {
        return 'vacancies_exhausted';
    }

    public function statusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }
}
