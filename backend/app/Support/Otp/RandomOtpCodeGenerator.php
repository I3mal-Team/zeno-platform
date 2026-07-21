<?php

declare(strict_types=1);

namespace App\Support\Otp;

use Random\RandomException;

final class RandomOtpCodeGenerator implements OtpCodeGenerator
{
    /** @throws RandomException */
    public function generate(int $length): string
    {
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }
}
