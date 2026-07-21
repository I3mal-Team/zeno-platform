<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * الطبقة الوحيدة التي تلمس قاعدة البيانات لكيان التصنيف.
 */
final class CategoryRepository
{
    /** @return Collection<int, Category> */
    public function allActive(): Collection
    {
        return Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    public function findByCode(string $code): ?Category
    {
        return Category::query()->where('code', $code)->first();
    }
}
