<?php

declare(strict_types=1);

namespace App\Support\Sms;

use App\Support\Integrations\StubDriver;
use Psr\Log\LoggerInterface;

final class LogSmsGateway implements SmsGateway, StubDriver
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(string $to, string $message): void
    {
        $this->logger->info('SMS (stub)', ['to' => $to, 'message' => $message]);
    }

    public function stubDescription(): string
    {
        return 'logs instead of sending, so no code ever reaches the user';
    }
}
