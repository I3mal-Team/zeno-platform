<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationDecided;
use App\Exceptions\Domain\ApplicationAlreadyDecidedException;
use App\Exceptions\Domain\OrganizationMissingException;
use App\Exceptions\Domain\VacanciesExhaustedException;
use App\Models\Application;
use App\Models\Job;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use App\Repositories\JobRepository;
use App\Repositories\OrganizationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApplicationReviewService
{
    public function __construct(
        private readonly ApplicationRepository $applications,
        private readonly JobRepository $jobs,
        private readonly OrganizationRepository $organizations,
    ) {}

    /**
     * @param  array<int, string>  $statuses
     * @return LengthAwarePaginator<int, Application>
     */
    public function listForJob(User $employer, string $jobUuid, array $statuses, int $perPage): LengthAwarePaginator
    {
        $job = $this->resolveJob($employer, $jobUuid);

        return $this->applications->paginateForJob($job->id, $statuses, $perPage);
    }

    /**
     * @param  array<int, string>  $statuses
     * @return LengthAwarePaginator<int, Application>
     */
    public function listForOrganization(User $employer, array $statuses, int $perPage): LengthAwarePaginator
    {
        return $this->applications->paginateForOrganization($this->organizationFor($employer)->id, $statuses, $perPage);
    }

    /** Opening an applicant for the first time moves the application into review. */
    public function view(User $employer, int $applicationId): Application
    {
        $application = $this->resolveApplication($employer, $applicationId);

        if ($application->status === ApplicationStatus::Submitted) {
            $from = $application->status;
            $application->fill(['status' => ApplicationStatus::Review->value, 'viewed_at' => now()]);

            DB::transaction(function () use ($application, $from, $employer) {
                $this->applications->save($application);
                $this->applications->recordStatusChange(
                    $application, $from, ApplicationStatus::Review, 'user', $employer->id,
                );
            });
        }

        return $application;
    }

    /**
     * @return array{application: Application, jobFilled: bool} jobFilled tells
     *                                                          the caller to
     *                                                          suggest closing
     *                                                          the listing (D-13)
     */
    public function accept(User $employer, int $applicationId): array
    {
        $application = $this->resolveApplication($employer, $applicationId);
        $this->assertDecidable($application);

        return DB::transaction(function () use ($application, $employer) {
            ['job' => $job, 'accepted' => $accepted] = $this->applications->lockJobWithAcceptedCount($application->job_id);

            if ($accepted >= $job->vacancies_count) {
                throw new VacanciesExhaustedException;
            }

            $decided = $this->decide($application, ApplicationStatus::Accepted, $employer);

            return ['application' => $decided, 'jobFilled' => $accepted + 1 >= $job->vacancies_count];
        });
    }

    public function reject(User $employer, int $applicationId, ?string $reason = null): Application
    {
        $application = $this->resolveApplication($employer, $applicationId);
        $this->assertDecidable($application);

        return DB::transaction(fn () => $this->decide($application, ApplicationStatus::Rejected, $employer, $reason));
    }

    private function decide(Application $application, ApplicationStatus $to, User $employer, ?string $reason = null): Application
    {
        $from = $application->status;
        $application->fill([
            'status' => $to->value,
            'decided_at' => now(),
            'decided_by_user_id' => $employer->id,
        ]);
        $this->applications->save($application);
        $this->applications->recordStatusChange($application, $from, $to, 'user', $employer->id, $reason);

        ApplicationDecided::dispatch($application);

        return $application;
    }

    private function assertDecidable(Application $application): void
    {
        if (! $application->status->isDecidable()) {
            throw new ApplicationAlreadyDecidedException;
        }
    }

    private function resolveJob(User $employer, string $jobUuid): Job
    {
        return $this->jobs->findForOrganizationByUuid($this->organizationFor($employer)->id, $jobUuid)
            ?? throw new NotFoundHttpException;
    }

    private function resolveApplication(User $employer, int $applicationId): Application
    {
        return $this->applications->findForOrganization($this->organizationFor($employer)->id, $applicationId)
            ?? throw new NotFoundHttpException;
    }

    private function organizationFor(User $employer): Organization
    {
        return $this->organizations->findForUser($employer->id) ?? throw new OrganizationMissingException;
    }
}
