<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlanAudience;
use App\Enums\PlanFeature;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repositories\PlanRepository;
use App\Repositories\SubscriptionRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Resolves which plan a user is on and what it grants. Everything the dashboard
 * configures (limits, switches via `entitlements`) is read through here, so
 * gates never hardcode a tier.
 */
final class PlanService
{
    public function __construct(
        private readonly PlanRepository $plans,
        private readonly SubscriptionRepository $subscriptions,
    ) {}

    /** The user's current plan for an audience: their live subscription, else the free default. */
    public function planFor(User $user, PlanAudience $audience): SubscriptionPlan
    {
        $subscription = $this->subscriptions->activeForUser($user->id);

        if ($subscription !== null && $subscription->plan->audience === $audience) {
            return $subscription->plan;
        }

        // A transient free plan (entitlements default to off/zero) keeps the app
        // running before any plans are configured.
        return $this->plans->defaultFor($audience)
            ?? new SubscriptionPlan(['audience' => $audience->value, 'entitlements' => []]);
    }

    /** The side of the marketplace a user buys from — derived from their role. */
    public function audienceFor(User $user): PlanAudience
    {
        return $user->role === UserRole::Employer->value
            ? PlanAudience::Employer
            : PlanAudience::Candidate;
    }

    /**
     * The active plans on offer to a user, cheapest first — what the "الباقات"
     * screen lists. Only the audience matching the user's role is shown.
     *
     * @return Collection<int, SubscriptionPlan>
     */
    public function offeringsFor(User $user): Collection
    {
        return $this->plans->activeFor($this->audienceFor($user));
    }

    /** The user's current live subscription (with its plan), or null on the free tier. */
    public function currentSubscription(User $user): ?Subscription
    {
        return $this->subscriptions->activeForUser($user->id);
    }

    /**
     * How many live listings the employer may keep, or null when no limit is
     * configured (the system runs unlimited until plans define one).
     */
    public function activeListingsLimit(User $employer): ?int
    {
        $limit = $this->planFor($employer, PlanAudience::Employer)
            ->feature(PlanFeature::ActiveListingsLimit);

        return $limit > 0 ? $limit : null;
    }
}
