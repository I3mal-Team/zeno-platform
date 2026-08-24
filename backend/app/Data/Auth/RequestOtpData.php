<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Enums\OtpPurpose;

final readonly class RequestOtpData
{
    public function __construct(
        public string $phoneE164,
        public OtpPurpose $purpose = OtpPurpose::Login,
    ) {}
}
