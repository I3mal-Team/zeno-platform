<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $application_id
 * @property int $candidate_id
 * @property int $organization_id
 * @property string $status
 * @property Carbon|null $last_message_at
 */
final class Conversation extends Model
{
    protected $fillable = [
        'uuid', 'application_id', 'candidate_id', 'organization_id',
        'status', 'last_message_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['last_message_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Application, $this> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /** @return BelongsTo<User, $this> */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** @return HasOne<Message, $this> */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany('created_at');
    }

    /** The user to notify when [$user] posts — the other side of the thread. */
    public function counterpartOf(User $user): ?User
    {
        return $user->id === $this->candidate_id
            ? $this->organization->owner()
            : $this->candidate;
    }
}
