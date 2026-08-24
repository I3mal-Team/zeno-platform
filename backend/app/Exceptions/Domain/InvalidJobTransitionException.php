<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Enums\JobStatus;

final class InvalidJobTransitionException extends DomainException
{
    public function __construct(
        private readonly JobStatus $from,
        private readonly JobStatus $to,
    ) {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'JOB_INVALID_TRANSITION';
    }

    protected function messageKey(): string
    {
        return 'job_invalid_transition';
    }

    /** @return array<string, mixed> */
    public function details(): array
    {
        return ['from' => $this->from->value, 'to' => $this->to->value];
    }
}
