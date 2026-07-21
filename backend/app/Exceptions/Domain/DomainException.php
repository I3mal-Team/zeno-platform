<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base for business-rule failures. Services throw these; the central renderer
 * turns them into the error envelope. Every subclass must have a matching
 * constant on the client, since the code is the contract, not the message.
 */
abstract class DomainException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('errors.'.$this->messageKey()));
    }

    abstract public function errorCode(): string;

    abstract protected function messageKey(): string;

    public function statusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /** @return array<string, mixed> */
    public function details(): array
    {
        return [];
    }
}
