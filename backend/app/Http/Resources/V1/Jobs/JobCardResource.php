<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Jobs;

use App\Models\Job;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Job */
final class JobCardResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $distanceMeters = $this->distance_meters ?? null;

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
                'logo_url' => $this->organization->getFirstMediaUrl(Organization::LOGO_COLLECTION) ?: null,
            ]),
            'status' => $this->status->value,
            'contact_channel' => $this->contact_channel->value,
            'is_saved' => (bool) ($this->is_saved ?? false),
            // Present only on the "nearby" feed, via the distance select.
            'distance_km' => $distanceMeters !== null
                ? round(((float) $distanceMeters) / 1000, 1)
                : null,
            'published_at' => $this->published_at?->toIso8601String(),
            // Present only on the employer's own list, via withCount.
            'applications_count' => $this->whenNotNull($this->live_applications_count ?? null),
        ];
    }
}
