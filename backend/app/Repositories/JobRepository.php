<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Jobs\JobData;
use App\Enums\JobStatus;
use App\Models\Job;
use App\Models\JobRevision;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class JobRepository
{
    private const LIST_RELATIONS = [
        'organization.media', 'category', 'workType', 'salaryUnit', 'district',
    ];

    private const DETAIL_RELATIONS = [
        'organization.media', 'category', 'workType', 'salaryUnit', 'city',
        'district', 'genderRequirement', 'nationalityRequirement',
    ];

    /** @return Collection<int, Job> */
    public function latestPublished(int $limit): Collection
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug): ?Job
    {
        return Job::query()
            ->published()
            ->with(self::DETAIL_RELATIONS)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * A paused or filled listing stays openable by its slug so an applicant
     * can still reach it from "my applications" (D-12); search never surfaces
     * it. The status list is derived from the enum so the rule lives in one
     * place rather than drifting between here and JobStatus.
     */
    public function findReachableBySlug(string $slug, ?int $savedBy = null): ?Job
    {
        $reachable = array_map(
            fn (JobStatus $status) => $status->value,
            array_filter(JobStatus::cases(), fn (JobStatus $status) => $status->isReachableByDirectLink()),
        );

        return Job::query()
            ->whereIn('status', $reachable)
            ->with(self::DETAIL_RELATIONS)
            ->withSavedFlag($savedBy)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Job>
     */
    public function searchPublished(array $filters, int $perPage, ?int $savedBy = null): LengthAwarePaginator
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->withSavedFlag($savedBy)
            ->when($filters['query'] ?? null, fn ($q, $term) => $q->where('title', 'ilike', "%{$term}%"))
            ->when($filters['category'] ?? null, fn ($q, $code) => $q->whereHas('category', fn ($c) => $c->where('code', $code)))
            ->when($filters['work_type'] ?? null, fn ($q, $code) => $q->whereHas('workType', fn ($w) => $w->where('code', $code)))
            ->when($filters['city'] ?? null, fn ($q, $id) => $q->where('city_id', $id))
            ->when($filters['salary_min'] ?? null, fn ($q, $min) => $q->where('salary_amount', '>=', $min))
            // "all" listings stay in the results for any choice — narrowing to
            // "women" must still surface a job open to everyone.
            ->when($filters['gender'] ?? null, fn ($q, $code) => $q->whereHas(
                'genderRequirement',
                fn ($g) => $g->whereIn('code', array_unique([$code, 'all'])),
            ))
            ->when($filters['nationality'] ?? null, fn ($q, $code) => $q->whereHas(
                'nationalityRequirement',
                fn ($n) => $n->whereIn('code', array_unique([$code, 'all'])),
            ))
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @return Collection<int, Job> */
    public function publishedForOrganization(int $organizationId, int $limit): Collection
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->where('organization_id', $organizationId)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Every listing an organization owns, in any status — the employer's own
     * management list, unlike the public feed.
     *
     * @return LengthAwarePaginator<int, Job>
     */
    public function paginateForOrganization(int $organizationId, ?string $status, int $perPage): LengthAwarePaginator
    {
        return Job::query()
            ->forOrganization($organizationId)
            ->with(self::LIST_RELATIONS)
            ->withCount(['applications as live_applications_count' => fn ($q) => $q->live()])
            ->when($status, fn ($q, $value) => $q->where('status', $value))
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForOrganizationByUuid(int $organizationId, string $uuid): ?Job
    {
        return Job::query()
            ->forOrganization($organizationId)
            ->with(self::DETAIL_RELATIONS)
            ->where('uuid', $uuid)
            ->first();
    }

    public function countForOrganization(int $organizationId, ?JobStatus $status = null, ?CarbonInterface $since = null): int
    {
        return Job::query()
            ->forOrganization($organizationId)
            ->when($status !== null, fn ($q) => $q->where('status', $status->value))
            ->when($since !== null, fn ($q) => $q->where('published_at', '>=', $since))
            ->count();
    }

    public function sumViewsForOrganization(int $organizationId): int
    {
        return (int) Job::query()->forOrganization($organizationId)->sum('views_count');
    }

    public function create(JobData $data, JobStatus $status, int $organizationId, int $userId): Job
    {
        $job = new Job([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $organizationId,
            'created_by_user_id' => $userId,
            'title' => $data->title,
            'slug' => $this->uniqueSlug($data->title),
            'description' => $data->description,
            'application_fields' => $data->applicationFields ?: null,
            'category_id' => $data->categoryId,
            'work_type_id' => $data->workTypeId,
            'salary_unit_id' => $data->salaryUnitId,
            'gender_requirement_id' => $data->genderRequirementId,
            'nationality_requirement_id' => $data->nationalityRequirementId,
            'salary_amount' => $data->salaryAmount,
            'salary_amount_max' => $data->salaryAmountMax,
            'hours_per_week' => $data->hoursPerWeek,
            'shift_note' => $data->shiftNote,
            'vacancies_count' => $data->vacanciesCount,
            'city_id' => $data->cityId,
            'district_id' => $data->districtId,
            'address_line' => $data->addressLine,
            'contact_channel' => $data->contactChannel->value,
            'status' => $status->value,
            'published_at' => $status === JobStatus::Active ? now() : null,
            'expires_at' => $data->expiresAt,
        ]);
        $job->save();

        $this->writeLocation($job, $data->latitude, $data->longitude);

        return $job;
    }

    /** @param  array<string, mixed>  $attributes */
    public function update(Job $job, JobData $data, array $attributes = []): Job
    {
        $renamed = $job->title !== $data->title;

        $job->fill([
            'title' => $data->title,
            'slug' => $renamed ? $this->uniqueSlug($data->title) : $job->slug,
            'description' => $data->description,
            'application_fields' => $data->applicationFields ?: null,
            'category_id' => $data->categoryId,
            'work_type_id' => $data->workTypeId,
            'salary_unit_id' => $data->salaryUnitId,
            'gender_requirement_id' => $data->genderRequirementId,
            'nationality_requirement_id' => $data->nationalityRequirementId,
            'salary_amount' => $data->salaryAmount,
            'salary_amount_max' => $data->salaryAmountMax,
            'hours_per_week' => $data->hoursPerWeek,
            'shift_note' => $data->shiftNote,
            'vacancies_count' => $data->vacanciesCount,
            'city_id' => $data->cityId,
            'district_id' => $data->districtId,
            'address_line' => $data->addressLine,
            'contact_channel' => $data->contactChannel->value,
            'expires_at' => $data->expiresAt,
            ...$attributes,
        ]);
        $job->save();

        $this->writeLocation($job, $data->latitude, $data->longitude);

        return $job;
    }

    /** @param  array<string, mixed>  $attributes */
    public function applyStatus(Job $job, JobStatus $status, array $attributes = []): Job
    {
        $job->fill(['status' => $status->value, ...$attributes])->save();

        return $job;
    }

    public function recordStatusChange(
        Job $job,
        ?JobStatus $from,
        JobStatus $to,
        string $actorType,
        ?int $actorId,
        ?string $reason = null,
    ): void {
        DB::table('job_status_history')->insert([
            'job_id' => $job->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }

    /** @param  array<string, array{from: mixed, to: mixed}>  $changes */
    public function createRevision(Job $job, ?int $userId, array $changes): JobRevision
    {
        return $job->revisions()->create([
            'edited_by_user_id' => $userId,
            'changes' => $changes,
        ]);
    }

    public function hasApplicants(Job $job): bool
    {
        return $job->applications()->live()->exists();
    }

    private function writeLocation(Job $job, ?float $latitude, ?float $longitude): void
    {
        if ($latitude !== null && $longitude !== null) {
            DB::statement(
                'UPDATE jobs SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?',
                [$longitude, $latitude, $job->id],
            );

            return;
        }

        // No precise pin — fall back to the district's centre, then the city's,
        // so every listing is placeable. COALESCE keeps an existing location, so
        // an edit that omits coordinates never clobbers a pin already set.
        DB::statement(
            'UPDATE jobs SET location = COALESCE(
                location,
                (SELECT center_point FROM districts WHERE districts.id = jobs.district_id),
                (SELECT center_point FROM cities WHERE cities.id = jobs.city_id)
            ) WHERE jobs.id = ?',
            [$job->id],
        );
    }

    /**
     * Published listings within `radiusMeters` of the point, nearest first, each
     * tagged with `distance_meters`. Uses the GIST index via ST_DWithin.
     *
     * @return LengthAwarePaginator<int, Job>
     */
    public function nearby(float $latitude, float $longitude, int $radiusMeters, int $perPage): LengthAwarePaginator
    {
        return Job::query()
            ->published()
            ->with(self::LIST_RELATIONS)
            ->whereNotNull('location')
            ->whereRaw('ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326), ?)', [$longitude, $latitude, $radiusMeters])
            ->select('jobs.*')
            ->selectRaw('ST_Distance(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)) AS distance_meters', [$longitude, $latitude])
            ->orderBy('distance_meters')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * The candidate's saved city centre as a search origin, so "nearby" still
     * works without a live GPS fix.
     *
     * @return object{lat: float, lng: float}|null
     */
    public function originForCandidate(int $candidateId): ?object
    {
        /** @var object{lat: float, lng: float}|null $row */
        // Prefer the candidate's own pinned location; fall back to their city
        // centre when they never set one.
        $row = DB::selectOne(
            'SELECT ST_Y(COALESCE(cp.location, c.center_point)::geometry) AS lat,
                    ST_X(COALESCE(cp.location, c.center_point)::geometry) AS lng
             FROM candidate_profiles cp
             LEFT JOIN cities c ON c.id = cp.city_id
             WHERE cp.user_id = ? AND COALESCE(cp.location, c.center_point) IS NOT NULL',
            [$candidateId],
        );

        return $row;
    }

    /**
     * Arabic titles transliterate to empty slugs, so the random tail is what
     * keeps the value unique and non-empty.
     */
    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        return ($base !== '' ? $base.'-' : 'job-').Str::lower(Str::random(8));
    }
}
