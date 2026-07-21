<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Profile;

use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CandidateProfile */
final class CandidateProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'full_name' => $this->full_name,
            'age' => $this->age,
            'birth_date' => $this->birth_date?->toDateString(),
            'nationality_code' => $this->nationality_code,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ]),
            'job_title' => $this->job_title,
            'years_of_experience' => $this->years_of_experience,
            'skills' => $this->skills,
            'bio' => $this->bio,
            'completion_percentage' => $this->completion_percentage,
            'avatar_url' => $this->getFirstMediaUrl(CandidateProfile::AVATAR_COLLECTION, 'thumb') ?: null,
        ];
    }
}
