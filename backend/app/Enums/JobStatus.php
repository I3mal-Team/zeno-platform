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

    /**
     * A paused or filled listing is unreachable through search yet still
     * openable by a candidate who already applied — the direct-link exception
     * of D-12. Filled belongs here for the same reason: someone whose
     * application is still open must be able to read what they applied to.
     */
    public function isReachableByDirectLink(): bool
    {
        return $this === self::Active || $this === self::Paused || $this === self::Filled;
    }

    /** A candidate may only apply while the listing is actively taking applicants. */
    public function acceptsApplications(): bool
    {
        return $this === self::Active;
    }

    /** Content stays editable until the listing is terminally closed or gone. */
    public function isEditable(): bool
    {
        return ! in_array($this, [self::Closed, self::Expired, self::Removed, self::Rejected], true);
    }

    /**
     * The transitions an employer may drive themselves. Moderation outcomes
     * (pending_review → active/rejected) and system events (expiry) live
     * outside this map.
     *
     * @return list<self>
     */
    public function employerTransitions(): array
    {
        return match ($this) {
            self::Active => [self::Paused, self::Filled, self::Closed],
            self::Paused => [self::Active, self::Closed],
            self::Filled => [self::Active, self::Closed],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->employerTransitions(), true);
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
