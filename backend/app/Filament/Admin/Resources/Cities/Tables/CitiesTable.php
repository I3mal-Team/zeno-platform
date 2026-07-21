<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Cities\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('name')->label('المدينة')->searchable(),
                TextColumn::make('region')->label('المنطقة'),
                TextColumn::make('districts_count')->label('الأحياء')->counts('districts'),
                IconColumn::make('is_active')->label('مفعّلة')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()]);
    }
}
