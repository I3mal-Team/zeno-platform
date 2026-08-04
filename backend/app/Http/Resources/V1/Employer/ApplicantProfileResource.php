<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employer;

use App\Models\Application;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The applicant's profile as the employer is allowed to see it (D-21): the
 * hireable fields only, never the national id.
 *
 * @mixin Application
 */
final class ApplicantProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var CandidateProfile|null $profile */
        $profile = $this->candidate->candidateProfile;

        return [
            'reference' => $this->reference_number,
            'status' => $this->status->value,
            'shortlisted' => $this->shortlisted,
            'note' => $this->employer_note,
            'contact_channel' => $this->contact_channel->value,
            'answers' => $this->answerSummary(),
            // The candidate's phone is released only once accepted (D-21).
            'candidate_phone' => $this->status->value === 'accepted' ? $this->candidate->phone_e164 : null,
            'profile' => $profile === null ? null : [
                'name' => $profile->full_name,
                'age' => $profile->age,
                'job_title' => $profile->job_title,
                'years_of_experience' => $profile->years_of_experience,
                'skills' => $profile->skills,
                'bio' => $profile->bio,
                'city' => $profile->relationLoaded('city') ? $profile->city?->name : null,
                'avatar_url' => $profile->getFirstMediaUrl(CandidateProfile::AVATAR_COLLECTION) ?: null,
                'resume_url' => $profile->getFirstMediaUrl(CandidateProfile::RESUME_COLLECTION) ?: null,
            ],
        ];
    }
}
