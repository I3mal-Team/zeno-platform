<?php

declare(strict_types=1);

namespace App\Data\Profile;

final readonly class CandidateProfileData
{
    /** @param  list<string>  $skills */
    public function __construct(
        public string $fullName,
        public ?string $nationalId = null,
        public ?string $nationalIdType = null,
        public ?string $birthDate = null,
        public ?string $gender = null,
        public ?string $nationalityCode = null,
        public ?int $cityId = null,
        public ?string $jobTitle = null,
        public ?int $yearsOfExperience = null,
        public array $skills = [],
        public ?string $bio = null,
    ) {}
}
