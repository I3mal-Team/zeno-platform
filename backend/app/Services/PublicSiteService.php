<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Job;
use App\Repositories\CategoryRepository;
use App\Repositories\JobRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class PublicSiteService
{
    public function __construct(
        private readonly JobRepository $jobs,
        private readonly CategoryRepository $categories,
    ) {}

    /** @return Collection<int, Job> */
    public function latestJobs(int $limit = 4): Collection
    {
        return $this->jobs->latestPublished($limit);
    }

    /** @return Collection<int, Category> */
    public function categoriesWithCounts(): Collection
    {
        return $this->categories->allActiveWithJobCounts();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Job>
     */
    public function searchJobs(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->jobs->searchPublished($filters, $perPage);
    }

    public function findJob(string $slug): ?Job
    {
        return $this->jobs->findPublishedBySlug($slug);
    }
}
