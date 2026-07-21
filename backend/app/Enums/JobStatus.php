<?php

declare(strict_types=1);

namespace App\Enums;

enum JobStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Paused = 'paused';
    case Filled = 'filled';
    case Expired = 'expired';
    case Closed = 'closed';
    case Rejected = 'rejected';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::PendingReview => 'قيد المراجعة',
            self::Active => 'نشط',
            self::Paused => 'متوقف',
            self::Filled => 'اكتمل',
            self::Expired => 'منتهي',
            self::Closed => 'مغلق',
            self::Rejected => 'مرفوض',
            self::Removed => 'محذوف',
        };
    }

    /** Only an active listing is discoverable by candidates. */
    public function isDiscoverable(): bool
    {
        return $this === self::Active;
    }

    public function badgeForeground(): string
    {
        return match ($this) {
            self::Active => '#1F7A3D',
            self::Paused, self::PendingReview => '#8A6D12',
            self::Rejected, self::Removed => '#B23232',
            default => '#5B6470',
        };
    }

    public function badgeBackground(): string
    {
        return match ($this) {
            self::Active => '#E3F3E8',
            self::Paused, self::PendingReview => '#FDF3D6',
            self::Rejected, self::Removed => '#FBE6E6',
            default => '#EEF1F4',
        };
    }
}
