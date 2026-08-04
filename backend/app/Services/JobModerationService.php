<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\JobStatus;
use App\Events\JobPublished;
use App\Exceptions\Domain\InvalidJobTransitionException;
use App\Models\Job;
use App\Repositories\JobRepository;
use App\Repositories\ModerationActionRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Support\Facades\DB;

/**
 * The admin side of D-15: reviewing the first listing of an untrusted
 * organization. Approving one grants the organization sticky trust so its
 * later listings publish without review.
 */
final class JobModerationService
{
    public function __construct(
        private readonly JobRepository $jobs,
        private readonly OrganizationRepository $organizations,
        private readonly ModerationActionRepository $actions,
    ) {}

    public function approve(int $adminId, Job $job): Job
    {
        $this->assertPending($job);

        $approved = DB::transaction(function () use ($adminId, $job) {
            $approved = $this->jobs->applyStatus($job, JobStatus::Active, ['published_at' => now()]);
            $this->jobs->recordStatusChange($approved, JobStatus::PendingReview, JobStatus::Active, 'admin', $adminId);
            $this->organizations->enableAutoPublish($approved->organization);
            $this->actions->record($adminId, 'job_approved', 'job', $approved->id, 'اعتماد أول إعلان');

            return $approved;
        });

        // Approval is the first time this listing is public — fan out to alerts.
        JobPublished::dispatch($approved);

        return $approved;
    }

    public function reject(int $adminId, Job $job, string $reason): Job
    {
        $this->assertPending($job);

        return DB::transaction(function () use ($adminId, $job, $reason) {
            $rejected = $this->jobs->applyStatus($job, JobStatus::Rejected);
            $this->jobs->recordStatusChange($rejected, JobStatus::PendingReview, JobStatus::Rejected, 'admin', $adminId, $reason);
            $this->actions->record($adminId, 'job_rejected', 'job', $rejected->id, $reason);

            return $rejected;
        });
    }

    private function assertPending(Job $job): void
    {
        if ($job->status !== JobStatus::PendingReview) {
            throw new InvalidJobTransitionException($job->status, JobStatus::Active);
        }
    }
}
