<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Countries\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('flag')->label('العلم'),
                TextColumn::make('name')->label('الدولة')->searchable(),
                TextColumn::make('iso2')->label('ISO2'),
                TextColumn::make('dial_code')->label('المفتاح'),
                IconColumn::make('is_active')->label('مفعّلة')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }
}
