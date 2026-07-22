<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationSubmitted;
use App\Exceptions\Domain\ApplicationAlreadyDecidedException;
use App\Exceptions\Domain\ApplicationAlreadyExistsException;
use App\Exceptions\Domain\JobNotActiveException;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApplicationService
{
    /** Days the employer keeps time-limited access to the applicant profile (D-21). */
    private const PROFILE_ACCESS_DAYS = 60;

    public function __construct(
        private readonly ApplicationRepository $applications,
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function apply(User $candidate, Job $job): Application
    {
        if (! $job->status->acceptsApplications()) {
            throw new JobNotActiveException;
        }

        $this->subscriptions->assertCanApply($candidate);

        if ($this->applications->liveExistsFor($job->id, $candidate->id)) {
            throw new ApplicationAlreadyExistsException;
        }

        $application = DB::transaction(function () use ($candidate, $job) {
            $application = $this->applications->create([
                'reference_number' => $this->applications->nextReference(),
                'job_id' => $job->id,
                'candidate_id' => $candidate->id,
                'organization_id' => $job->organization_id,
                'status' => ApplicationStatus::Submitted->value,
                'contact_channel' => $job->contact_channel->value,
                'profile_access_token' => (string) Str::ulid(),
                'profile_access_expires_at' => now()->addDays(self::PROFILE_ACCESS_DAYS),
            ]);

            $this->applications->recordStatusChange(
                $application, null, ApplicationStatus::Submitted, 'user', $candidate->id,
            );

            return $application;
        });

        ApplicationSubmitted::dispatch($application);

        return $application;
    }

    public function withdraw(User $candidate, string $reference): Application
    {
        $application = $this->applications->findForCandidateByReference($candidate->id, $reference);

        if ($application === null) {
            throw new ApplicationAlreadyDecidedException;
        }

        if (! $application->status->isDecidable()) {
            throw new ApplicationAlreadyDecidedException;
        }

        $from = $application->status;
        $application->fill(['status' => ApplicationStatus::Withdrawn->value, 'withdrawn_at' => now()]);

        return DB::transaction(function () use ($application, $from, $candidate) {
            $this->applications->save($application);
            $this->applications->recordStatusChange(
                $application, $from, ApplicationStatus::Withdrawn, 'user', $candidate->id,
            );

            return $application;
        });
    }

    /** @return Collection<int, Application> */
    public function listForCandidate(User $candidate): Collection
    {
        return $this->applications->forCandidate($candidate->id);
    }
}
