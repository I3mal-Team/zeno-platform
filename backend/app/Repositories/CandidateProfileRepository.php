<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Profile\CandidateProfileData;
use App\Models\CandidateProfile;
use Illuminate\Support\Facades\DB;

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
        $attributes = [
            'full_name' => $data->fullName,
            'birth_date' => $data->birthDate,
            'gender' => $data->gender,
            'nationality_code' => $data->nationalityCode,
            'city_id' => $data->cityId,
            'job_title' => $data->jobTitle,
            'years_of_experience' => $data->yearsOfExperience,
            'skills' => $data->skills,
            'bio' => $data->bio,
            'completion_percentage' => $completion,
        ];

        // The national id is write-only and never returned to the client, so an
        // edit that leaves it blank must keep the stored value rather than wipe it.
        if ($data->nationalId !== null) {
            $attributes['national_id'] = $data->nationalId;
            $attributes['national_id_type'] = $data->nationalIdType;
        }

        $profile = CandidateProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            $attributes,
        );

        // A precise home location is optional; when given it drives the "nearby"
        // origin instead of the city centre. Omitting it keeps any existing pin.
        if ($data->latitude !== null && $data->longitude !== null) {
            DB::statement(
                'UPDATE candidate_profiles SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
                [$data->longitude, $data->latitude, $profile->id],
            );
        }

        return $profile;
    }
}
