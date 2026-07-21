<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Lean placeholder for the sprint-5 application flow. It exists now so a job
 * edit can find and notify the candidates who already applied (D-14).
 *
 * @property int $id
 * @property string $reference_number
 * @property int $job_id
 * @property int $candidate_id
 * @property int $organization_id
 * @property string $status
 * @property Carbon|null $withdrawn_at
 * @property Job $job
 * @property User $candidate
 */
class Application extends Model
{
    /** Statuses that still represent a candidate awaiting an outcome. */
    public const LIVE_STATUSES = ['submitted', 'review', 'accepted'];

    protected $fillable = [
        'reference_number', 'job_id', 'candidate_id', 'organization_id',
        'status', 'contact_channel', 'profile_access_token',
        'profile_access_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'profile_access_expires_at' => 'datetime',
            'viewed_at' => 'datetime',
            'decided_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /** @return BelongsTo<User, $this> */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /** @param  Builder<Application>  $query */
    public function scopeLive(Builder $query): void
    {
        $query->whereIn('status', self::LIVE_STATUSES);
    }
}
