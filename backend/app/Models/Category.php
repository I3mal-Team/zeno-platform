<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $icon
 * @property string $color_token
 * @property int $jobs_count
 */
class Category extends Model
{
    use HasTranslations;

    protected $fillable = ['code', 'name', 'icon', 'color_token', 'sort_order', 'is_active'];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return HasMany<Job, $this> */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /** @param  Builder<Category>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Icon tile colours from the design handover, keyed by colour token. */
    private const TINTS = [
        'amber' => ['#8A6D12', '#FDF1CC'],
        'green' => ['#4F7A2E', '#E6F0E1'],
        'red' => ['#B2453A', '#F8E3E1'],
        'purple' => ['#6A4E8A', '#ECE6F4'],
        'blue' => ['#2E6E8A', '#E2EEF4'],
        'teal' => ['#2E7A6B', '#E0EFEC'],
        'navy' => ['#2E5A8A', '#E2EAF4'],
        'brown' => ['#8A4E2E', '#F4E8E2'],
        'cyan' => ['#2E7A8A', '#E0EDF0'],
        'neutral' => ['#5A554C', '#EDEAE3'],
    ];

    public function tintForeground(): string
    {
        return (self::TINTS[$this->color_token] ?? self::TINTS['neutral'])[0];
    }

    public function tintBackground(): string
    {
        return (self::TINTS[$this->color_token] ?? self::TINTS['neutral'])[1];
    }
}
