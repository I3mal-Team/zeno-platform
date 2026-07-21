<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class ConversationNotAllowedException extends DomainException
{
    public function errorCode(): string
    {
        return 'CONVERSATION_NOT_ALLOWED';
    }

    protected function messageKey(): string
    {
        return 'conversation_not_allowed';
    }

    public function statusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
