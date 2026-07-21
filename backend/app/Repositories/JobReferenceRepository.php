<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\GenderRequirement;
use App\Models\NationalityRequirement;
use App\Models\SalaryUnit;
use App\Models\WorkType;
use Illuminate\Database\Eloquent\Collection;

/** The small taxonomies a listing references, served for the publish form. */
final class JobReferenceRepository
{
    /** @return Collection<int, WorkType> */
    public function workTypes(): Collection
    {
        return WorkType::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    /** @return Collection<int, SalaryUnit> */
    public function salaryUnits(): Collection
    {
        return SalaryUnit::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    /** @return Collection<int, GenderRequirement> */
    public function genderRequirements(): Collection
    {
        return GenderRequirement::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    /** @return Collection<int, NationalityRequirement> */
    public function nationalityRequirements(): Collection
    {
        return NationalityRequirement::query()->where('is_active', true)->orderBy('sort_order')->get();
    }
}
