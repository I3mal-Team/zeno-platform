<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $iso2
 * @property string $dial_code
 * @property string $name
 * @property string $flag
 * @property string|null $placeholder
 * @property bool $is_active
 */
class Country extends Model
{
    protected $fillable = ['iso2', 'dial_code', 'name', 'flag', 'placeholder', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @param  Builder<Country>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    public function label(): string
    {
        return $this->flag.' '.$this->dial_code;
    }
}
