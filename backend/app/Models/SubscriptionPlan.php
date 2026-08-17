<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlanAudience;
use App\Enums\PlanFeature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * A sellable plan for either side of the marketplace. Everything the dashboard
 * controls lives in `entitlements` (feature key => value); the keys are defined
 * by App\Enums\PlanFeature.
 *
 * @property int $id
 * @property string $code
 * @property PlanAudience $audience
 * @property string $name
 * @property string $price
 * @property string $currency
 * @property int $duration_days
 * @property array<string, mixed> $entitlements
 * @property bool $is_active
 */
class SubscriptionPlan extends Model
{
    use HasTranslations;

    protected $fillable = [
        'code', 'audience', 'name', 'price', 'currency',
        'duration_days', 'entitlements', 'is_active',
    ];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'audience' => PlanAudience::class,
            'entitlements' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** The value this plan grants for a feature, falling back to its default. */
    public function feature(PlanFeature $feature): int|bool
    {
        $value = $this->entitlements[$feature->value] ?? null;

        if ($value === null) {
            return $feature->default();
        }

        return $feature->type() === 'int' ? (int) $value : (bool) $value;
    }

    public function allows(PlanFeature $feature): bool
    {
        return (bool) $this->feature($feature);
    }

    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }

    /** @param  Builder<SubscriptionPlan>  $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<SubscriptionPlan>  $query */
    public function scopeForAudience(Builder $query, PlanAudience $audience): void
    {
        $query->where('audience', $audience->value);
    }
}
