<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\SubscriptionPlans\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('code')->label('الكود')->badge(),
                TextColumn::make('audience')->label('الطرف')->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('price')->label('السعر')->money('SAR')->sortable(),
                TextColumn::make('duration_days')->label('المدة')->suffix(' يوم'),
                IconColumn::make('is_active')->label('مفعّلة')->boolean(),
            ])
            ->defaultSort('audience')
            ->recordActions([EditAction::make()]);
    }
}
