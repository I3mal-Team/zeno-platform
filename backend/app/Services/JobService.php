<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Jobs\JobData;
use App\Enums\JobStatus;
use App\Events\JobMateriallyChanged;
use App\Exceptions\Domain\InvalidJobTransitionException;
use App\Exceptions\Domain\JobNotEditableException;
use App\Exceptions\Domain\OrganizationMissingException;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\JobRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class JobService
{
    public function __construct(
        private readonly JobRepository $jobs,
        private readonly OrganizationRepository $organizations,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Job>
     */
    public function browse(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->jobs->searchPublished($filters, $perPage);
    }

    /** Resolves a listing for a candidate, including the paused direct-link case (D-12). */
    public function findPublicBySlug(string $slug): ?Job
    {
        return $this->jobs->findReachableBySlug($slug);
    }

    /** @return LengthAwarePaginator<int, Job> */
    public function listForEmployer(User $user, ?string $status, int $perPage): LengthAwarePaginator
    {
        return $this->jobs->paginateForOrganization($this->organizationFor($user)->id, $status, $perPage);
    }

    public function findForEmployer(User $user, string $uuid): ?Job
    {
        return $this->jobs->findForOrganizationByUuid($this->organizationFor($user)->id, $uuid);
    }

    /**
     * Publishing an offer either goes live at once or waits in review, decided
     * by the organization's standing (D-15). There is no separate draft step:
     * the app publishes in one action.
     */
    public function publish(User $user, JobData $data): Job
    {
        $organization = $this->organizationFor($user);

        // TEMPORARY (integrations.jobs.auto_publish_all): force every listing
        // live, bypassing the first-listing review gate (D-15).
        $status = $organization->mayPublishWithoutReview() || config('integrations.jobs.auto_publish_all')
            ? JobStatus::Active
            : JobStatus::PendingReview;

        return DB::transaction(function () use ($data, $status, $organization, $user) {
            $job = $this->jobs->create($data, $status, $organization->id, $user->id);
            $this->jobs->recordStatusChange($job, null, $status, 'user', $user->id);

            return $job;
        });
    }

    /**
     * Editing is always allowed while the listing is not terminally closed.
     * Material changes to a listing that already has applicants are recorded
     * and every applicant is notified (D-14).
     */
    public function update(User $user, Job $job, JobData $data): Job
    {
        if (! $job->status->isEditable()) {
            throw new JobNotEditableException;
        }

        $material = $this->materialChanges($job, $data);
        $notify = $material !== [] && $this->jobs->hasApplicants($job);

        return DB::transaction(function () use ($user, $job, $data, $material, $notify) {
            $updated = $this->jobs->update($job, $data);

            if ($notify) {
                $revision = $this->jobs->createRevision($updated, $user->id, $material);
                JobMateriallyChanged::dispatch($updated, $revision);
            }

            return $updated;
        });
    }

    public function pause(User $user, Job $job): Job
    {
        return $this->transition($user, $job, JobStatus::Paused);
    }

    public function resume(User $user, Job $job): Job
    {
        return $this->transition($user, $job, JobStatus::Active);
    }

    public function markFilled(User $user, Job $job): Job
    {
        return $this->transition($user, $job, JobStatus::Filled, closedReason: 'filled');
    }

    public function close(User $user, Job $job, ?string $reason = null): Job
    {
        return $this->transition($user, $job, JobStatus::Closed, closedReason: $reason ?? 'by_employer');
    }

    private function transition(User $user, Job $job, JobStatus $target, ?string $closedReason = null): Job
    {
        if (! $job->status->canTransitionTo($target)) {
            throw new InvalidJobTransitionException($job->status, $target);
        }

        $from = $job->status;
        $attributes = $this->transitionAttributes($job, $target, $closedReason);

        return DB::transaction(function () use ($job, $target, $from, $user, $attributes) {
            $updated = $this->jobs->applyStatus($job, $target, $attributes);
            $this->jobs->recordStatusChange($updated, $from, $target, 'user', $user->id);

            return $updated;
        });
    }

    /** @return array<string, mixed> */
    private function transitionAttributes(Job $job, JobStatus $target, ?string $closedReason): array
    {
        return match ($target) {
            // Resuming a never-published listing stamps its first publish time.
            JobStatus::Active => $job->published_at === null ? ['published_at' => now()] : [],
            JobStatus::Filled, JobStatus::Closed => ['closed_at' => now(), 'closed_reason' => $closedReason],
            default => [],
        };
    }

    /**
     * @return array<string, array{from: mixed, to: mixed}> the material fields
     *                                                      whose value moved
     */
    private function materialChanges(Job $job, JobData $data): array
    {
        $incoming = [
            'salary_amount' => $data->salaryAmount,
            'salary_amount_max' => $data->salaryAmountMax,
            'work_type_id' => $data->workTypeId,
            'city_id' => $data->cityId,
            'district_id' => $data->districtId,
            'vacancies_count' => $data->vacanciesCount,
            'contact_channel' => $data->contactChannel->value,
        ];

        $changes = [];

        foreach ($incoming as $field => $new) {
            $current = $this->normalize($job->getAttribute($field));
            $next = $this->normalize($new);

            if ($current !== $next) {
                $changes[$field] = ['from' => $current, 'to' => $next];
            }
        }

        return $changes;
    }

    /** Compares numbers by value and enums by their backing string. */
    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return is_numeric($value) ? (float) $value : $value;
    }

    private function organizationFor(User $user): Organization
    {
        return $this->organizations->findForUser($user->id)
            ?? throw new OrganizationMissingException;
    }
}
