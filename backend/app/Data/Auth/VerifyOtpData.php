<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Enums\OtpPurpose;
use App\Enums\UserRole;

final readonly class VerifyOtpData
{
    public function __construct(
        public string $phoneE164,
        public string $code,
        public UserRole $role,
        public OtpPurpose $purpose = OtpPurpose::Login,
    ) {}
}
