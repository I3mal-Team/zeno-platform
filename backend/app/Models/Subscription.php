<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A user's subscription to a plan. One active row per user (DB-enforced).
 *
 * @property int $id
 * @property int $user_id
 * @property int $subscription_plan_id
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $expires_at
 * @property bool $auto_renew
 * @property SubscriptionPlan $plan
 */
class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status',
        'started_at', 'expires_at', 'auto_renew',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    /** @return BelongsTo<SubscriptionPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isLive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
