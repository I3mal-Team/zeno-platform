<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

final class CountryRepository
{
    /** @return Collection<int, Country> */
    public function allActive(): Collection
    {
        return Country::query()->active()->get();
    }

    public function findActiveByIso(string $iso2): ?Country
    {
        return Country::query()->active()->where('iso2', $iso2)->first();
    }
}
