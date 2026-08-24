<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * One stored status per D-03; the label differs by who is looking. `new` is a
 * viewer's perspective (an unopened submission), never a stored value.
 */
enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case Review = 'review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    /** What the candidate sees. */
    public function candidateLabel(): string
    {
        return match ($this) {
            self::Submitted, self::Review => 'قيد المراجعة',
            self::Accepted => 'مقبول',
            self::Rejected => 'غير مقبول',
            self::Withdrawn => 'مسحوب',
        };
    }

    /** What the employer sees; an unopened submission reads as "new". */
    public function employerLabel(): string
    {
        return match ($this) {
            self::Submitted => 'طلب جديد',
            self::Review => 'قيد المراجعة',
            self::Accepted => 'مقبول',
            self::Rejected => 'مرفوض',
            self::Withdrawn => 'مسحوب',
        };
    }

    public function badgeForeground(): string
    {
        return match ($this) {
            self::Accepted => '#1F7A3D',
            self::Submitted, self::Review => '#8A6D12',
            self::Rejected => '#B23232',
            self::Withdrawn => '#5B6470',
        };
    }

    public function badgeBackground(): string
    {
        return match ($this) {
            self::Accepted => '#E3F3E8',
            self::Submitted, self::Review => '#FDF3D6',
            self::Rejected => '#FBE6E6',
            self::Withdrawn => '#EEF1F4',
        };
    }

    /** A live application is still awaiting or holds an outcome. */
    public function isLive(): bool
    {
        return in_array($this, [self::Submitted, self::Review, self::Accepted], true);
    }

    /** The employer may still decide only before a terminal outcome. */
    public function isDecidable(): bool
    {
        return $this === self::Submitted || $this === self::Review;
    }
}
