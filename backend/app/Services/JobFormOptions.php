<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\GenderRequirement;
use App\Models\NationalityRequirement;
use App\Models\SalaryUnit;
use App\Models\WorkType;
use Illuminate\Database\Eloquent\Collection;

final readonly class JobFormOptions
{
    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<int, WorkType>  $workTypes
     * @param  Collection<int, SalaryUnit>  $salaryUnits
     * @param  Collection<int, GenderRequirement>  $genderRequirements
     * @param  Collection<int, NationalityRequirement>  $nationalityRequirements
     */
    public function __construct(
        public Collection $categories,
        public Collection $workTypes,
        public Collection $salaryUnits,
        public Collection $genderRequirements,
        public Collection $nationalityRequirements,
    ) {}
}
