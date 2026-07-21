<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Districts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DistrictsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الحي')->searchable(),
                TextColumn::make('city.name')->label('المدينة')->sortable(),
                TextColumn::make('code')->label('الكود')->badge(),
                IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->filters([
                SelectFilter::make('city_id')->label('المدينة')->relationship('city', 'code'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }
}
