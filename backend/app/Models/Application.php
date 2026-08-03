<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ContactChannel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $reference_number
 * @property int $job_id
 * @property int $candidate_id
 * @property int $organization_id
 * @property ApplicationStatus $status
 * @property ContactChannel $contact_channel
 * @property string $profile_access_token
 * @property Carbon $profile_access_expires_at
 * @property Carbon|null $viewed_at
 * @property Carbon|null $decided_at
 * @property Carbon|null $withdrawn_at
 * @property Carbon $created_at
 * @property Job $job
 * @property User $candidate
 * @property Organization $organization
 */
class Application extends Model implements HasMedia
{
    use InteractsWithMedia;

    /** Statuses that still represent a candidate awaiting an outcome. */
    public const LIVE_STATUSES = ['submitted', 'review', 'accepted'];

    /** Media collection holding one uploaded file/image answer per field key. */
    public static function answerCollection(string $fieldKey): string
    {
        return 'answer_'.$fieldKey;
    }

    protected $fillable = [
        'reference_number', 'job_id', 'candidate_id', 'organization_id',
        'status', 'contact_channel', 'answers', 'profile_access_token',
        'profile_access_expires_at', 'viewed_at', 'decided_at',
        'decided_by_user_id', 'withdrawn_at',
    ];

    protected $attributes = [
        'status' => 'submitted',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'contact_channel' => ContactChannel::class,
            'answers' => 'array',
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

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Present only once the application has been accepted (D-19), so the
     * applicants list uses it to decide whether a "message" action exists.
     *
     * @return HasOne<Conversation, $this>
     */
    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    /** @param  Builder<Application>  $query */
    public function scopeLive(Builder $query): void
    {
        $query->whereIn('status', self::LIVE_STATUSES);
    }

    /** @param  Builder<Application>  $query */
    public function scopeForOrganization(Builder $query, int $organizationId): void
    {
        $query->where('organization_id', $organizationId);
    }

    /**
     * The employer may see the candidate's profile while the application is
     * live and the token has not expired (D-21). Withdrawal or rejection ends
     * it, and access is always scoped to the owning organization.
     */
    public function profileAccessActive(): bool
    {
        return $this->status->isLive() && $this->profile_access_expires_at->isFuture();
    }
}
