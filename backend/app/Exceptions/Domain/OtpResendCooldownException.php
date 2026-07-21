<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Symfony\Component\HttpFoundation\Response;

final class OtpResendCooldownException extends DomainException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'OTP_RESEND_COOLDOWN';
    }

    protected function messageKey(): string
    {
        return 'otp_resend_cooldown';
    }

    public function statusCode(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }

    /** The client renders a countdown, so the remaining time must travel. */
    public function details(): array
    {
        return ['retry_after_seconds' => $this->retryAfterSeconds];
    }
}
