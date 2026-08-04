<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Jobs;

use App\Models\Job;
use App\Models\Organization;
use App\Support\PublicMedia;
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
                'logo_url' => PublicMedia::url($this->organization->getFirstMediaUrl(Organization::LOGO_COLLECTION) ?: null),
            ]),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'views_count' => $this->views_count,
            'is_saved' => (bool) ($this->is_saved ?? false),
            'is_open_for_applications' => $this->status->acceptsApplications(),
            'contact_channel' => $this->contact_channel->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            // Raw foreign keys so the employer's edit form can preselect every
            // dropdown. Location is left out — a null on update keeps the pin.
            'edit' => [
                'category_id' => $this->category_id,
                'work_type_id' => $this->work_type_id,
                'salary_unit_id' => $this->salary_unit_id,
                'gender_requirement_id' => $this->gender_requirement_id,
                'nationality_requirement_id' => $this->nationality_requirement_id,
                'city_id' => $this->city_id,
                'district_id' => $this->district_id,
                'salary_amount' => (float) $this->salary_amount,
                'salary_amount_max' => $this->salary_amount_max !== null ? (float) $this->salary_amount_max : null,
                'hours_per_week' => $this->hours_per_week,
                'shift_note' => $this->shift_note,
                'vacancies_count' => $this->vacancies_count,
                'address_line' => $this->address_line,
            ],
        ];
    }
}
