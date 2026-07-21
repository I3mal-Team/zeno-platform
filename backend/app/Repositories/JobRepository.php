<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class JobRepository
{
    private const LIST_RELATIONS = [
        'organization', 'category', 'workType', 'salaryUnit', 'district',
    ];

    /** @return Collection<int, Job> */
    public function latestPublished(int $limit): Collection
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug): ?Job
    {
        return Job::query()
            ->published()
            ->with([...self::LIST_RELATIONS, 'city', 'genderRequirement', 'nationalityRequirement'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Job>
     */
    public function searchPublished(array $filters, int $perPage): LengthAwarePaginator
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->when($filters['query'] ?? null, fn ($q, $term) => $q->where('title', 'ilike', "%{$term}%"))
            ->when($filters['category'] ?? null, fn ($q, $code) => $q->whereHas('category', fn ($c) => $c->where('code', $code)))
            ->when($filters['work_type'] ?? null, fn ($q, $code) => $q->whereHas('workType', fn ($w) => $w->where('code', $code)))
            ->when($filters['city'] ?? null, fn ($q, $id) => $q->where('city_id', $id))
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return Collection<int, Job> */
    public function publishedForOrganization(int $organizationId, int $limit): Collection
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->where('organization_id', $organizationId)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
