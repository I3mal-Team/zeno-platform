<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class ApplicationRepository
{
    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): Application
    {
        return Application::query()->create($attributes);
    }

    /** Pronounceable, DB-unique reference drawn from a Postgres sequence. */
    public function nextReference(): string
    {
        /** @var object{n: int} $row */
        $row = DB::selectOne("SELECT nextval('application_reference_seq') AS n");

        return 'ZN'.$row->n;
    }

    public function liveExistsFor(int $jobId, int $candidateId): bool
    {
        return Application::query()
            ->where('job_id', $jobId)
            ->where('candidate_id', $candidateId)
            ->where('status', '!=', ApplicationStatus::Withdrawn->value)
            ->exists();
    }

    /** @return Collection<int, Application> */
    public function forCandidate(int $candidateId): Collection
    {
        return Application::query()
            ->with(['job.organization', 'job.category', 'job.city'])
            ->where('candidate_id', $candidateId)
            ->latest('created_at')
            ->get();
    }

    public function findForCandidateByReference(int $candidateId, string $reference): ?Application
    {
        return Application::query()
            ->where('candidate_id', $candidateId)
            ->where('reference_number', $reference)
            ->first();
    }

    /**
     * @param  array<int, string>  $statuses
     * @return LengthAwarePaginator<int, Application>
     */
    public function paginateForJob(int $jobId, array $statuses, int $perPage): LengthAwarePaginator
    {
        return Application::query()
            ->with('candidate.candidateProfile')
            ->where('job_id', $jobId)
            ->when($statuses !== [], fn ($q) => $q->whereIn('status', $statuses))
            ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * @param  array<int, string>  $statuses
     * @return LengthAwarePaginator<int, Application>
     */
    public function paginateForOrganization(int $organizationId, array $statuses, int $perPage): LengthAwarePaginator
    {
        return Application::query()
            ->with(['candidate.candidateProfile', 'job'])
            ->forOrganization($organizationId)
            ->when($statuses !== [], fn ($q) => $q->whereIn('status', $statuses))
            ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findForOrganization(int $organizationId, int $id): ?Application
    {
        return Application::query()
            ->with(['candidate.candidateProfile.city', 'job'])
            ->forOrganization($organizationId)
            ->whereKey($id)
            ->first();
    }

    public function save(Application $application): Application
    {
        $application->save();

        return $application;
    }

    /**
     * Locks the job row and counts its accepted applications, so two concurrent
     * acceptances cannot both slip past the vacancy limit (D-13).
     *
     * @return array{job: Job, accepted: int}
     */
    public function lockJobWithAcceptedCount(int $jobId): array
    {
        /** @var Job $job */
        $job = Job::query()->whereKey($jobId)->lockForUpdate()->firstOrFail();

        $accepted = Application::query()
            ->where('job_id', $jobId)
            ->where('status', ApplicationStatus::Accepted->value)
            ->count();

        return ['job' => $job, 'accepted' => $accepted];
    }

    public function recordStatusChange(
        Application $application,
        ?ApplicationStatus $from,
        ApplicationStatus $to,
        string $actorType,
        ?int $actorId,
        ?string $reason = null,
    ): void {
        DB::table('application_status_history')->insert([
            'application_id' => $application->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
