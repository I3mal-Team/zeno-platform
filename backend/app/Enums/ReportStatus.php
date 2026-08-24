<?php

declare(strict_types=1);

namespace App\Enums;

/** Matches the `status` CHECK constraint on `reports`. */
enum ReportStatus: string
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جديد',
            self::Reviewing => 'قيد المراجعة',
            self::Resolved => 'تمّت المعالجة',
            self::Dismissed => 'مرفوض',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New => 'danger',
            self::Reviewing => 'warning',
            self::Resolved => 'success',
            self::Dismissed => 'gray',
        };
    }

    /** A report still needing an admin's attention. */
    public function isOpen(): bool
    {
        return $this === self::New || $this === self::Reviewing;
    }
}
