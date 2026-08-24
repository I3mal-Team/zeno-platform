<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

final class CityRepository
{
    /** @return Collection<int, City> */
    public function allActive(): Collection
    {
        return City::query()->active()->orderBy('sort_order')->get();
    }

    /** @return Collection<int, District> */
    public function districtsFor(int $cityId): Collection
    {
        return District::query()
            ->active()
            ->where('city_id', $cityId)
            ->orderBy('sort_order')
            ->get();
    }
}
