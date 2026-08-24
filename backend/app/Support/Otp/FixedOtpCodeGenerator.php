<?php

declare(strict_types=1);

namespace App\Support\Otp;

use App\Support\Integrations\StubDriver;

final class FixedOtpCodeGenerator implements OtpCodeGenerator, StubDriver
{
    public function __construct(private readonly string $code) {}

    public function generate(int $length): string
    {
        return str_pad($this->code, $length, '0', STR_PAD_LEFT);
    }

    public function stubDescription(): string
    {
        return 'returns a constant code, so any phone number can sign in';
    }
}
