<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Job;
use App\Models\Organization;
use App\Repositories\CategoryRepository;
use App\Repositories\JobRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class PublicSiteService
{
    public function __construct(
        private readonly JobRepository $jobs,
        private readonly CategoryRepository $categories,
        private readonly OrganizationRepository $organizations,
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

    /**
     * An organization's public page, with the listings a visitor may actually
     * apply to. A suspended organization has no public page at all.
     *
     * @return array{organization: Organization, jobs: Collection<int, Job>}|null
     */
    public function findCompany(string $slug): ?array
    {
        $organization = $this->organizations->findPublicBySlug($slug);

        return $organization === null ? null : [
            'organization' => $organization,
            'jobs' => $this->jobs->publishedForOrganization($organization->id, 20),
        ];
    }
}
