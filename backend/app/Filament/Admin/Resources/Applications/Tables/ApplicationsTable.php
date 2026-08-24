<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')->label('رقم الطلب')->searchable()->copyable(),
                TextColumn::make('job.title')->label('الوظيفة')->searchable()->limit(32),
                TextColumn::make('candidate.candidateProfile.full_name')->label('المتقدّم')
                    ->description(fn (Application $record): string => $record->candidate->phone_e164)
                    ->searchable(),
                TextColumn::make('organization.name')->label('المنشأة')->searchable()->limit(28),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (ApplicationStatus $state): string => $state->employerLabel())
                    ->color(fn (ApplicationStatus $state): string => match ($state) {
                        ApplicationStatus::Accepted => 'success',
                        ApplicationStatus::Submitted, ApplicationStatus::Review => 'warning',
                        ApplicationStatus::Rejected => 'danger',
                        ApplicationStatus::Withdrawn => 'gray',
                    }),
                TextColumn::make('created_at')->label('التقديم')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options([
                    ApplicationStatus::Submitted->value => 'طلب جديد',
                    ApplicationStatus::Review->value => 'قيد المراجعة',
                    ApplicationStatus::Accepted->value => 'مقبول',
                    ApplicationStatus::Rejected->value => 'مرفوض',
                    ApplicationStatus::Withdrawn->value => 'مسحوب',
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
