<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Billing;

use App\Enums\PlanFeature;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SubscriptionPlan */
final class PlanResource extends JsonResource
{
    private ?int $currentPlanId = null;

    /** Marks the plan the caller is currently subscribed to, so clients can badge it. */
    public function current(?int $currentPlanId): static
    {
        $this->currentPlanId = $currentPlanId;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'audience' => $this->audience->value,
            'name' => $this->name,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'duration_days' => $this->duration_days,
            'is_free' => $this->isFree(),
            'is_current' => $this->currentPlanId !== null && $this->id === $this->currentPlanId,
            'features' => array_map(
                fn (PlanFeature $feature) => [
                    'key' => $feature->value,
                    'label' => $feature->label(),
                    'type' => $feature->type(),
                    'value' => $this->feature($feature),
                    'enabled' => $this->allows($feature),
                ],
                PlanFeature::forAudience($this->audience),
            ),
        ];
    }
}
