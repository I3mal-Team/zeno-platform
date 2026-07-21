<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

final class JobNotEditableException extends DomainException
{
    public function errorCode(): string
    {
        return 'JOB_NOT_EDITABLE';
    }

    protected function messageKey(): string
    {
        return 'job_not_editable';
    }
}
