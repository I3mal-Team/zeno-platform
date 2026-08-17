<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

/** The employer has as many live listings as their plan allows. */
final class PlanLimitReachedException extends DomainException
{
    public function __construct(private readonly int $limit)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'PLAN_LIMIT_REACHED';
    }

    protected function messageKey(): string
    {
        return 'plan_listing_limit_reached';
    }

    /** @return array<string, mixed> */
    public function details(): array
    {
        return ['limit' => $this->limit];
    }
}
