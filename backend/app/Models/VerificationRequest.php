<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * An organization's application to be marked verified. Supporting documents
 * hang off the media collection rather than columns, because what counts as
 * proof differs between an individual and a company.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $submitted_by_user_id
 * @property VerificationRequestStatus $status
 * @property int|null $reviewed_by_admin_id
 * @property Carbon|null $reviewed_at
 * @property string|null $review_note
 */
final class VerificationRequest extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const DOCUMENTS_COLLECTION = 'documents';

    protected $fillable = [
        'organization_id', 'submitted_by_user_id', 'status',
        'reviewed_by_admin_id', 'reviewed_at', 'review_note',
    ];

    protected $attributes = ['status' => 'pending'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => VerificationRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /** @return BelongsTo<Admin, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::DOCUMENTS_COLLECTION)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }
}
