<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A candidate's saved search. When a job is published, any alert whose facets
 * all match (a null facet matches anything) notifies its owner.
 *
 * @property int $id
 * @property int $candidate_id
 * @property string|null $keyword
 * @property int|null $category_id
 * @property int|null $city_id
 * @property int|null $work_type_id
 * @property Carbon $created_at
 */
class JobAlert extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'candidate_id', 'keyword', 'category_id', 'city_id', 'work_type_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsTo<WorkType, $this> */
    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }
}
