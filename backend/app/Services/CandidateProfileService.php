<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Profile\CandidateProfileData;
use App\Models\CandidateProfile;
use App\Models\User;
use App\Repositories\CandidateProfileRepository;
use Illuminate\Http\UploadedFile;

final class CandidateProfileService
{
    /**
     * Weighted so the fields employers actually screen on move the number
     * more than the optional ones.
     */
    private const COMPLETION_WEIGHTS = [
        'fullName' => 25,
        'cityId' => 15,
        'jobTitle' => 20,
        'yearsOfExperience' => 10,
        'skills' => 15,
        'bio' => 10,
        'birthDate' => 5,
    ];

    public function __construct(private readonly CandidateProfileRepository $profiles) {}

    public function find(User $user): ?CandidateProfile
    {
        return $this->profiles->findByUserId($user->id);
    }

    public function save(User $user, CandidateProfileData $data): CandidateProfile
    {
        return $this->profiles->upsert($user->id, $data, $this->completion($data));
    }

    public function saveAvatar(CandidateProfile $profile, UploadedFile $file): void
    {
        $profile->addMedia($file)->toMediaCollection(CandidateProfile::AVATAR_COLLECTION);
    }

    public function saveResume(CandidateProfile $profile, UploadedFile $file): void
    {
        $profile->addMedia($file)->toMediaCollection(CandidateProfile::RESUME_COLLECTION);
    }

    private function completion(CandidateProfileData $data): int
    {
        $filled = [
            'fullName' => $data->fullName !== '',
            'cityId' => $data->cityId !== null,
            'jobTitle' => $data->jobTitle !== null && $data->jobTitle !== '',
            'yearsOfExperience' => $data->yearsOfExperience !== null,
            'skills' => $data->skills !== [],
            'bio' => $data->bio !== null && $data->bio !== '',
            'birthDate' => $data->birthDate !== null,
        ];

        $score = 0;

        foreach ($filled as $field => $isFilled) {
            if ($isFilled) {
                $score += self::COMPLETION_WEIGHTS[$field];
            }
        }

        return $score;
    }
}
