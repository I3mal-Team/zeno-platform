<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The lifecycle of one submission. Distinct from VerificationStatus, which is
 * the organization's standing: a rejected request leaves the organization
 * unverified and free to submit again.
 */
enum VerificationRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
