<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Jobs;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Job */
final class JobCardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'salary' => [
                'amount' => (float) $this->salary_amount,
                'amount_max' => $this->salary_amount_max !== null ? (float) $this->salary_amount_max : null,
                'currency' => $this->salary_currency,
                'formatted' => $this->formattedSalary(),
                'unit' => $this->whenLoaded('salaryUnit', fn () => $this->salaryUnit->name),
            ],
            'category' => $this->whenLoaded('category', fn () => [
                'code' => $this->category->code,
                'name' => $this->category->name,
                'color_token' => $this->category->color_token,
            ]),
            'work_type' => $this->whenLoaded('workType', fn () => $this->workType->name),
            'city' => $this->whenLoaded('city', fn () => $this->city->name),
            'district' => $this->whenLoaded('district', fn () => $this->district?->name),
            'organization' => $this->whenLoaded('organization', fn () => [
                'name' => $this->organization->name,
                'is_verified' => $this->organization->isVerified(),
            ]),
            'status' => $this->status->value,
            'contact_channel' => $this->contact_channel->value,
            'published_at' => $this->published_at?->toIso8601String(),
            // Present only on the employer's own list, via withCount.
            'applications_count' => $this->whenNotNull($this->live_applications_count ?? null),
        ];
    }
}
