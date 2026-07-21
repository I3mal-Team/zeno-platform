<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

final class CatalogService
{
    public function __construct(private readonly CategoryRepository $categories) {}

    /** @return Collection<int, Category> */
    public function activeCategories(): Collection
    {
        return $this->categories->allActive();
    }
}
