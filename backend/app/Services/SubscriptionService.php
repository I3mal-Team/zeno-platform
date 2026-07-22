<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Domain\SubscriptionRequiredException;
use App\Models\User;

/**
 * The single gate D-01 leaves open: applying is free at launch, so this always
 * allows it. When a paid candidate plan is introduced, flip REQUIRED on and
 * implement the real check here — every caller already routes through it.
 */
final class SubscriptionService
{
    public function assertCanApply(User $candidate): void
    {
        if ($this->required() && ! $this->hasActivePlan($candidate)) {
            throw new SubscriptionRequiredException;
        }
    }

    private function required(): bool
    {
        return (bool) config('integrations.candidate_subscription_required', false);
    }

    private function hasActivePlan(User $candidate): bool
    {
        // No billing module yet; the single plan is the free one.
        return true;
    }
}
