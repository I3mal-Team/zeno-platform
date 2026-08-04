<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SavedJobRepository
{
    /** Loaded for the saved-jobs list so the cards render like the feed. */
    private const RELATIONS = [
        'organization.media', 'category', 'workType', 'salaryUnit', 'district',
    ];

    public function add(User $candidate, Job $job): void
    {
        $candidate->savedJobs()->syncWithoutDetaching([$job->id]);
    }

    public function remove(User $candidate, Job $job): void
    {
        $candidate->savedJobs()->detach($job->id);
    }

    public function exists(User $candidate, Job $job): bool
    {
        return $candidate->savedJobs()->whereKey($job->id)->exists();
    }

    /** @return LengthAwarePaginator<int, Job> */
    public function paginateFor(User $candidate, int $perPage): LengthAwarePaginator
    {
        return Job::query()
            ->select('jobs.*')
            ->join('saved_jobs', 'saved_jobs.job_id', '=', 'jobs.id')
            ->where('saved_jobs.candidate_id', $candidate->id)
            ->with(self::RELATIONS)
            ->withSavedFlag($candidate->id)
            ->orderByDesc('saved_jobs.created_at')
            ->paginate($perPage);
    }
}
