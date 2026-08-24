<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Billing;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Subscription */
final class SubscriptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'is_live' => $this->isLive(),
            'started_at' => $this->started_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'auto_renew' => $this->auto_renew,
            'plan' => new PlanResource($this->plan),
        ];
    }
}
