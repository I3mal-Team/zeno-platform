<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Profile\CandidateProfileData;
use App\Models\CandidateProfile;

final class CandidateProfileRepository
{
    public function findByUserId(int $userId): ?CandidateProfile
    {
        return CandidateProfile::query()
            ->with('city')
            ->where('user_id', $userId)
            ->first();
    }

    public function upsert(int $userId, CandidateProfileData $data, int $completion): CandidateProfile
    {
        return CandidateProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'full_name' => $data->fullName,
                'national_id' => $data->nationalId,
                'national_id_type' => $data->nationalIdType,
                'birth_date' => $data->birthDate,
                'gender' => $data->gender,
                'nationality_code' => $data->nationalityCode,
                'city_id' => $data->cityId,
                'job_title' => $data->jobTitle,
                'years_of_experience' => $data->yearsOfExperience,
                'skills' => $data->skills,
                'bio' => $data->bio,
                'completion_percentage' => $completion,
            ],
        );
    }
}
