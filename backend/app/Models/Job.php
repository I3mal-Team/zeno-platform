<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactChannel;
use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property array<int, array<string, mixed>>|null $application_fields
 * @property string $salary_amount
 * @property string|null $salary_amount_max
 * @property int $vacancies_count
 * @property JobStatus $status
 * @property ContactChannel $contact_channel
 * @property Carbon|null $published_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $closed_at
 * @property string|null $closed_reason
 * @property int $views_count
 * @property int $organization_id
 * @property Organization $organization
 * @property Category $category
 * @property WorkType $workType
 * @property SalaryUnit $salaryUnit
 * @property City $city
 * @property District|null $district
 */
class Job extends Model
{
    use SoftDeletes;

    /**
     * Fields whose change after a listing has applicants alters the offer
     * itself, so each applicant must be told (D-14). Value is the label used
     * in the change record and notification.
     */
    public const MATERIAL_FIELDS = [
        'salary_amount' => 'الراتب',
        'salary_amount_max' => 'الحد الأعلى للراتب',
        'work_type_id' => 'نوع الدوام',
        'city_id' => 'المدينة',
        'district_id' => 'الحي',
        'vacancies_count' => 'عدد الشواغر',
        'contact_channel' => 'طريقة التواصل',
    ];

    protected $fillable = [
        'uuid', 'organization_id', 'created_by_user_id', 'title', 'slug',
        'description', 'application_fields', 'category_id', 'work_type_id',
        'salary_unit_id', 'gender_requirement_id', 'nationality_requirement_id',
        'salary_amount', 'salary_amount_max', 'salary_currency',
        'hours_per_week', 'shift_note', 'vacancies_count',
        'city_id', 'district_id', 'address_line',
        'contact_channel', 'status', 'published_at', 'expires_at',
        'closed_at', 'closed_reason',
    ];

    protected $attributes = [
        'status' => 'draft',
        'salary_currency' => 'SAR',
        'contact_channel' => 'app',
        'vacancies_count' => 1,
        'views_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'contact_channel' => ContactChannel::class,
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
            'vacancies_count' => 'integer',
            'views_count' => 'integer',
            'hours_per_week' => 'integer',
            'application_fields' => 'array',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<WorkType, $this> */
    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    /** @return BelongsTo<SalaryUnit, $this> */
    public function salaryUnit(): BelongsTo
    {
        return $this->belongsTo(SalaryUnit::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsTo<District, $this> */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /** @return BelongsTo<GenderRequirement, $this> */
    public function genderRequirement(): BelongsTo
    {
        return $this->belongsTo(GenderRequirement::class);
    }

    /** @return BelongsTo<NationalityRequirement, $this> */
    public function nationalityRequirement(): BelongsTo
    {
        return $this->belongsTo(NationalityRequirement::class);
    }

    /** @return HasMany<Application, $this> */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** @return HasMany<JobRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(JobRevision::class);
    }

    /** @param  Builder<Job>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', JobStatus::Active->value)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** @param  Builder<Job>  $query */
    public function scopeForOrganization(Builder $query, int $organizationId): void
    {
        $query->where('organization_id', $organizationId);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function formattedSalary(): string
    {
        $amount = number_format((float) $this->salary_amount);

        return $this->salary_amount_max !== null
            ? $amount.' – '.number_format((float) $this->salary_amount_max)
            : $amount;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
