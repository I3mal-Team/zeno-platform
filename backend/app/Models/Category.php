<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

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

    /** @param  Builder<Category>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
