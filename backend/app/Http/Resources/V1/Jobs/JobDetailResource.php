<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Jobs;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Job */
final class JobDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'application_fields' => $this->application_fields ?? [],
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
            'hours_per_week' => $this->hours_per_week,
            'shift_note' => $this->shift_note,
            'vacancies_count' => $this->vacancies_count,
            'requirements' => [
                'gender' => $this->whenLoaded('genderRequirement', fn () => $this->genderRequirement->name),
                'nationality' => $this->whenLoaded('nationalityRequirement', fn () => $this->nationalityRequirement->name),
            ],
            'location' => [
                'city' => $this->whenLoaded('city', fn () => $this->city->name),
                'district' => $this->whenLoaded('district', fn () => $this->district?->name),
                'address_line' => $this->address_line,
            ],
            'organization' => $this->whenLoaded('organization', fn () => [
                'id' => $this->organization->uuid,
                'name' => $this->organization->name,
                'is_verified' => $this->organization->isVerified(),
            ]),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_open_for_applications' => $this->status->acceptsApplications(),
            'contact_channel' => $this->contact_channel->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
