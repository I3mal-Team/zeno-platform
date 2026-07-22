<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employer;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Application */
final class ApplicantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $profile = $this->candidate->relationLoaded('candidateProfile')
            ? $this->candidate->candidateProfile
            : null;

        return [
            'id' => $this->id,
            'reference' => $this->reference_number,
            'status' => $this->status->value,
            'status_label' => $this->status->employerLabel(),
            'is_new' => $this->status->value === 'submitted',
            'applied_at' => $this->created_at->toIso8601String(),
            'candidate' => [
                'name' => $profile?->full_name,
                'job_title' => $profile?->job_title,
                'years_of_experience' => $profile?->years_of_experience,
            ],
        ];
    }
}
