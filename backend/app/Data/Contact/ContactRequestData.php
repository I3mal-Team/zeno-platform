<?php

declare(strict_types=1);

namespace App\Data\Contact;

final readonly class ContactRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $topic,
        public string $message,
        public ?string $ipAddress = null,
    ) {}
}
