<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
class GenderRequirement extends Model
{
    use HasTranslations;

    protected $table = 'gender_requirements';

    protected $fillable = ['code', 'name', 'sort_order', 'is_active'];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @param  Builder<GenderRequirement>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
