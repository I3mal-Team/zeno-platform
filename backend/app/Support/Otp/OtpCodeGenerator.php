<?php

declare(strict_types=1);

namespace App\Support\Otp;

interface OtpCodeGenerator
{
    public function generate(int $length): string;
}
