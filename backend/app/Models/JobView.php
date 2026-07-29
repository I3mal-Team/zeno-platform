<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One row per viewer per listing per day — the table's unique index enforces
 * that, so a refresh does not inflate the count.
 *
 * @property int $id
 * @property int $job_id
 * @property int|null $viewer_user_id
 * @property string $session_hash
 * @property Carbon $viewed_at
 */
final class JobView extends Model
{
    /** The table carries only viewed_at (DB default); there is no updated_at. */
    public $timestamps = false;

    protected $fillable = ['job_id', 'viewer_user_id', 'session_hash', 'viewed_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['viewed_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /** @return BelongsTo<User, $this> */
    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }
}
