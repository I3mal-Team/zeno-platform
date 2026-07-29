<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CandidateProfiles\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CandidateProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->label('الاسم')->searchable()->weight('bold'),
                TextColumn::make('user.phone_e164')->label('الجوال')->toggleable(),
                TextColumn::make('city.name')->label('المدينة')->placeholder('—'),
                TextColumn::make('job_title')->label('المهنة')->placeholder('—')->limit(28),
                TextColumn::make('years_of_experience')->label('الخبرة')->suffix(' سنة')->placeholder('—'),
                TextColumn::make('nationality_code')->label('الجنسية')->placeholder('—'),
                TextColumn::make('completion_percentage')->label('الاكتمال')->badge()
                    ->formatStateUsing(fn (int $state): string => $state.'%')
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 40 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('التسجيل')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
