<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Repositories\CategoryRepository;
use App\Repositories\CityRepository;
use Illuminate\Database\Eloquent\Collection;

final class CatalogService
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CityRepository $cities,
    ) {}

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
}
