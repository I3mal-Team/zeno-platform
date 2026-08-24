<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SubscriptionPlans\Schemas;

use App\Enums\PlanAudience;
use App\Enums\PlanFeature;
use App\Models\SubscriptionPlan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('الكود')
                ->required()
                ->maxLength(40)
                ->unique(ignoreRecord: true)
                // The code is a stable key; changing it would orphan subscriptions.
                ->disabled(fn (?SubscriptionPlan $record) => $record !== null)
                ->dehydrated(),
            Select::make('audience')
                ->label('الطرف')
                ->required()
                ->options(collect(PlanAudience::cases())
                    ->mapWithKeys(fn (PlanAudience $a) => [$a->value => $a->label()])
                    ->all()),
            TextInput::make('name.ar')->label('الاسم')->required()->maxLength(60),
            TextInput::make('price')->label('السعر (﷼)')->numeric()->required()->default(0),
            TextInput::make('duration_days')->label('المدة (أيام)')->numeric()->required()->default(30),
            Toggle::make('is_active')->label('مفعّلة')->default(true),

            ...self::entitlementFields(),
        ]);
    }

    /** @return list<TextInput|Toggle> */
    private static function entitlementFields(): array
    {
        return array_map(
            fn (PlanFeature $feature) => $feature->type() === 'int'
                ? TextInput::make('entitlements.'.$feature->value)
                    ->label($feature->label())
                    ->helperText($feature->audience()->label())
                    ->numeric()
                    ->default(0)
                : Toggle::make('entitlements.'.$feature->value)
                    ->label($feature->label())
                    ->helperText($feature->audience()->label())
                    ->default(false),
            PlanFeature::cases(),
        );
    }
}
