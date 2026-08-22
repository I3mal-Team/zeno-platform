<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Job;
use App\Models\JobAlert;
use Illuminate\Database\Eloquent\Collection;

final class JobAlertRepository
{
    private const RELATIONS = ['category', 'city', 'workType'];

    /** @param  array<string, mixed>  $attributes */
    public function create(int $candidateId, array $attributes): JobAlert
    {
        return JobAlert::query()->create([...$attributes, 'candidate_id' => $candidateId]);
    }

    /** @return Collection<int, JobAlert> */
    public function forCandidate(int $candidateId): Collection
    {
        return JobAlert::query()
            ->with(self::RELATIONS)
            ->where('candidate_id', $candidateId)
            ->latest('created_at')
            ->get();
    }

    public function deleteForCandidate(int $candidateId, int $id): void
    {
        JobAlert::query()
            ->where('candidate_id', $candidateId)
            ->whereKey($id)
            ->delete();
    }

    /**
     * Alerts a freshly published job satisfies — a null facet matches anything,
     * and the keyword (if set) must appear in the title.
     *
     * @return Collection<int, JobAlert>
     */
    public function matching(Job $job): Collection
    {
        return JobAlert::query()
            ->with('candidate')
            ->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $job->category_id))
            ->where(fn ($q) => $q->whereNull('city_id')->orWhere('city_id', $job->city_id))
            ->where(fn ($q) => $q->whereNull('work_type_id')->orWhere('work_type_id', $job->work_type_id))
            ->where(fn ($q) => $q->whereNull('keyword')->orWhereRaw("? ILIKE '%' || keyword || '%'", [$job->title]))
            ->get();
    }
}
