<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportTarget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property ReportTarget $target_type
 * @property int $sort_order
 * @property bool $is_active
 */
final class ReportReason extends Model
{
    use HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

    protected $fillable = ['code', 'name', 'target_type', 'sort_order', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_type' => ReportTarget::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @param  Builder<ReportReason>  $query */
    public function scopeActiveFor(Builder $query, ReportTarget $target): void
    {
        $query->where('is_active', true)
            ->where('target_type', $target->value)
            ->orderBy('sort_order');
    }
}
