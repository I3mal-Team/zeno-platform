<?php

declare(strict_types=1);

namespace App\Data\Jobs;

use App\Enums\ContactChannel;
use Illuminate\Support\Carbon;

final readonly class JobData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $categoryId,
        public int $workTypeId,
        public int $salaryUnitId,
        public int $genderRequirementId,
        public int $nationalityRequirementId,
        public float $salaryAmount,
        public ?float $salaryAmountMax,
        public ?int $hoursPerWeek,
        public ?string $shiftNote,
        public int $vacanciesCount,
        public int $cityId,
        public ?int $districtId,
        public ?string $addressLine,
        public ?float $latitude,
        public ?float $longitude,
        public ContactChannel $contactChannel,
        public ?Carbon $expiresAt,
        /** @var list<array<string, mixed>> */
        public array $applicationFields = [],
    ) {}
}
