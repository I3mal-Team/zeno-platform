<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $salary_amount
 * @property string|null $salary_amount_max
 * @property int $vacancies_count
 * @property JobStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $expires_at
 * @property int $views_count
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

    protected $fillable = [
        'uuid', 'organization_id', 'created_by_user_id', 'title', 'slug',
        'description', 'category_id', 'work_type_id', 'salary_unit_id',
        'gender_requirement_id', 'nationality_requirement_id',
        'salary_amount', 'salary_amount_max', 'salary_currency',
        'hours_per_week', 'shift_note', 'vacancies_count',
        'city_id', 'district_id', 'address_line',
        'contact_channel', 'status', 'published_at', 'expires_at',
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
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
            'vacancies_count' => 'integer',
            'views_count' => 'integer',
            'hours_per_week' => 'integer',
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

    /** @param  Builder<Job>  $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', JobStatus::Active->value)
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
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
