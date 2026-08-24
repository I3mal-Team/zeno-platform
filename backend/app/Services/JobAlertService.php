<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Job;
use App\Models\JobAlert;
use App\Models\User;
use App\Repositories\JobAlertRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class JobAlertService
{
    public function __construct(private readonly JobAlertRepository $alerts) {}

    /** @param  array<string, mixed>  $filters */
    public function create(User $candidate, array $filters): JobAlert
    {
        return $this->alerts->create($candidate->id, $filters);
    }

    /** @return Collection<int, JobAlert> */
    public function listFor(User $candidate): Collection
    {
        return $this->alerts->forCandidate($candidate->id);
    }

    public function delete(User $candidate, int $id): void
    {
        $this->alerts->deleteForCandidate($candidate->id, $id);
    }

    /**
     * The candidates to notify about a newly published job — one entry per
     * person even if several of their alerts match.
     *
     * @return SupportCollection<int, User>
     */
    public function candidatesToNotify(Job $job): SupportCollection
    {
        return $this->alerts->matching($job)
            ->pluck('candidate')
            ->filter()
            ->unique('id')
            ->values();
    }
}
