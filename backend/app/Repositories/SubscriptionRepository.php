<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Subscription;

final class SubscriptionRepository
{
    /** The user's current live subscription, with its plan loaded. */
    public function activeForUser(int $userId): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest('id')
            ->first();
    }
}
