<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use App\Repositories\SavedJobRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SavedJobService
{
    public function __construct(private readonly SavedJobRepository $saved) {}

    public function save(User $candidate, Job $job): void
    {
        $this->saved->add($candidate, $job);
    }

    public function remove(User $candidate, Job $job): void
    {
        $this->saved->remove($candidate, $job);
    }

    public function isSaved(User $candidate, Job $job): bool
    {
        return $this->saved->exists($candidate, $job);
    }

    /** @return LengthAwarePaginator<int, Job> */
    public function listFor(User $candidate, int $perPage): LengthAwarePaginator
    {
        // The query tags every row with `is_saved` (true here by definition).
        return $this->saved->paginateFor($candidate, $perPage);
    }
}
