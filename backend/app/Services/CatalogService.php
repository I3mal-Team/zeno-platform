<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use App\Repositories\CountryRepository;
use App\Repositories\JobReferenceRepository;
use Illuminate\Database\Eloquent\Collection;

final class CatalogService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CityRepository $cities,
        private readonly CountryRepository $countries,
        private readonly JobReferenceRepository $jobReferences,
    ) {}

    /** @return Collection<int, Country> */
    public function activeCountries(): Collection
    {
        return $this->countries->allActive();
    }

    /** @return Collection<int, Category> */
    public function activeCategories(): Collection
    {
        return $this->categories->allActive();
    }

    /** @return Collection<int, City> */
    public function activeCities(): Collection
    {
        return $this->cities->allActive();
    }

    /** @return Collection<int, District> */
    public function districtsFor(int $cityId): Collection
    {
        return $this->cities->districtsFor($cityId);
    }

    /** The taxonomies a publish form needs beyond categories and cities. */
    public function jobFormOptions(): JobFormOptions
    {
        return new JobFormOptions(
            categories: $this->categories->allActive(),
            workTypes: $this->jobReferences->workTypes(),
            salaryUnits: $this->jobReferences->salaryUnits(),
            genderRequirements: $this->jobReferences->genderRequirements(),
            nationalityRequirements: $this->jobReferences->nationalityRequirements(),
        );
    }
}
