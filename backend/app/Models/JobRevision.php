<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $job_id
 * @property int|null $edited_by_user_id
 * @property array<string, array{from: mixed, to: mixed}> $changes
 * @property bool $applicants_notified
 * @property Carbon $created_at
 * @property Job $job
 */
class JobRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'job_id', 'edited_by_user_id', 'changes', 'applicants_notified',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'applicants_notified' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
