<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\PlanAudience;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;

final class PlanRepository
{
    /** The free fallback plan an audience gets without a paid subscription. */
    public function defaultFor(PlanAudience $audience): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()
            ->active()
            ->forAudience($audience)
            ->where('price', 0)
            ->orderBy('id')
            ->first();
    }

    /** @return Collection<int, SubscriptionPlan> */
    public function activeFor(PlanAudience $audience): Collection
    {
        return SubscriptionPlan::query()
            ->active()
            ->forAudience($audience)
            ->orderBy('price')
            ->get();
    }
}
