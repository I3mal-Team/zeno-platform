<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class SubscriptionRequiredException extends DomainException
{
    public function errorCode(): string
    {
        return 'SUBSCRIPTION_REQUIRED';
    }

    protected function messageKey(): string
    {
        return 'subscription_required';
    }

    public function statusCode(): int
    {
        return Response::HTTP_PAYMENT_REQUIRED;
    }
}
