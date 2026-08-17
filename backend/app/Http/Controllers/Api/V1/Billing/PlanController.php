<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\V1\Billing\PlanResource;
use App\Http\Resources\V1\Billing\SubscriptionResource;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlanController extends ApiController
{
    public function __construct(private readonly PlanService $plans) {}

    /** The plans on offer to the caller (by role), cheapest first, current one flagged. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentPlanId = $this->plans->currentSubscription($user)?->subscription_plan_id;

        $plans = $this->plans->offeringsFor($user)
            ->map(fn ($plan) => (new PlanResource($plan))->current($currentPlanId));

        return $this->successResponse($plans);
    }

    /** The caller's current standing: their subscription (if any) and effective plan. */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $this->plans->currentSubscription($user);
        $plan = $this->plans->planFor($user, $this->plans->audienceFor($user));

        return $this->successResponse([
            'subscription' => $subscription !== null ? new SubscriptionResource($subscription) : null,
            'plan' => (new PlanResource($plan))->current($subscription?->subscription_plan_id),
        ]);
    }
}
