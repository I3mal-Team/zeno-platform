<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationType;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string $uuid
 * @property OrganizationType $type
 * @property string $name
 * @property string $slug
 * @property string|null $responsible_person_name
 * @property string|null $commercial_registration
 * @property string|null $about
 * @property VerificationStatus $verification_status
 * @property bool $auto_publish_jobs
 * @property City|null $city
 */
class Organization extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    public const LOGO_COLLECTION = 'logo';

    protected $fillable = [
        'uuid', 'type', 'name', 'slug', 'commercial_registration',
        'responsible_person_name', 'city_id', 'about',
        'verification_status', 'auto_publish_jobs', 'status',
    ];

    protected $hidden = ['commercial_registration'];

    /** Mirrors the column defaults so a freshly created model is complete. */
    protected $attributes = [
        'verification_status' => 'unverified',
        'auto_publish_jobs' => false,
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'verification_status' => VerificationStatus::class,
            'auto_publish_jobs' => 'boolean',
            'commercial_registration' => 'encrypted',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role', 'joined_at');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    /**
     * A listing skips review when the organization is verified or has already
     * had one approved — the sticky trust of D-15.
     */
    public function mayPublishWithoutReview(): bool
    {
        return $this->isVerified() || $this->auto_publish_jobs;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::LOGO_COLLECTION)->singleFile();
    }
}
