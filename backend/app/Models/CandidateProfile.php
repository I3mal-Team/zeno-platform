<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property string|null $national_id
 * @property Carbon|null $birth_date
 * @property string|null $nationality_code
 * @property string|null $job_title
 * @property int|null $years_of_experience
 * @property array<int, string> $skills
 * @property string|null $bio
 * @property int $completion_percentage
 * @property City|null $city
 */
class CandidateProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const AVATAR_COLLECTION = 'avatar';

    protected $fillable = [
        'user_id', 'full_name', 'national_id', 'national_id_type', 'birth_date',
        'nationality_code', 'city_id', 'job_title', 'years_of_experience',
        'skills', 'bio', 'completion_percentage',
    ];

    protected $hidden = ['national_id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'skills' => 'array',
            'national_id' => 'encrypted',
            'years_of_experience' => 'integer',
            'completion_percentage' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::AVATAR_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(240)->height(240);
    }
}
