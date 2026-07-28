<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Job;
use Carbon\CarbonInterface;
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
    public function paginateForOrganization(int $organizationId, array $statuses, int $perPage, ?string $search = null): LengthAwarePaginator
    {
        return Application::query()
            ->with(['candidate.candidateProfile.city', 'job', 'conversation'])
            ->forOrganization($organizationId)
            ->when($statuses !== [], fn ($q) => $q->whereIn('status', $statuses))
            ->when(
                $search !== null && $search !== '',
                fn ($q) => $q->where(fn ($inner) => $inner
                    ->whereHas('candidate.candidateProfile', fn ($p) => $p->where('full_name', 'ILIKE', '%'.$search.'%'))
                    ->orWhere('reference_number', 'ILIKE', '%'.$search.'%')),
            )
            ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
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

    /** @param  array<int, string>  $statuses */
    public function countForOrganization(int $organizationId, array $statuses = [], ?CarbonInterface $since = null): int
    {
        return Application::query()
            ->forOrganization($organizationId)
            ->when($statuses !== [], fn ($q) => $q->whereIn('status', $statuses))
            ->when($since !== null, fn ($q) => $q->where('created_at', '>=', $since))
            ->count();
    }

    /**
     * Applications per calendar day, in the viewer's timezone so a submission
     * late at night lands on the day the employer saw it.
     *
     * @return array<string, int> Y-m-d => count, only for days that had any
     */
    public function dailyCountsForOrganization(int $organizationId, CarbonInterface $since, string $timezone): array
    {
        return Application::query()
            ->forOrganization($organizationId)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at AT TIME ZONE ?) AS day, COUNT(*) AS total', [$timezone])
            ->groupBy('day')
            ->pluck('total', 'day')
            ->all();
    }

    /**
     * Decisions taken in a window, keyed on decided_at rather than created_at
     * so a decision counts to the week it was made, not the week the
     * application arrived.
     *
     * @return array{accepted: int, rejected: int}
     */
    public function decisionCountsForOrganization(int $organizationId, ?CarbonInterface $from = null, ?CarbonInterface $until = null): array
    {
        $counts = Application::query()
            ->forOrganization($organizationId)
            ->whereIn('status', [ApplicationStatus::Accepted->value, ApplicationStatus::Rejected->value])
            ->when($from !== null, fn ($q) => $q->where('decided_at', '>=', $from))
            ->when($until !== null, fn ($q) => $q->where('decided_at', '<', $until))
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'accepted' => (int) $counts->get(ApplicationStatus::Accepted->value, 0),
            'rejected' => (int) $counts->get(ApplicationStatus::Rejected->value, 0),
        ];
    }

    /** @return Collection<int, Application> */
    public function recentForOrganization(int $organizationId, int $limit): Collection
    {
        return Application::query()
            ->with(['candidate.candidateProfile.city', 'job'])
            ->forOrganization($organizationId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
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
