<?php

declare(strict_types=1);

namespace App\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'غير موثّقة',
            self::Pending => 'قيد المراجعة',
            self::Verified => 'موثّقة',
            self::Rejected => 'مرفوضة',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Unverified => 'gray',
            self::Pending => 'warning',
            self::Verified => 'success',
            self::Rejected => 'danger',
        };
    }
}
