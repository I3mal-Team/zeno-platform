<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Jobs\Tables;

use App\Enums\JobStatus;
use App\Models\Job;
use App\Services\JobModerationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable()->limit(40),
                TextColumn::make('organization.name')->label('المنشأة')->searchable(),
                TextColumn::make('category.name')->label('التصنيف'),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (JobStatus $state) => $state->label())
                    ->color(fn (JobStatus $state) => match ($state) {
                        JobStatus::Active => 'success',
                        JobStatus::PendingReview, JobStatus::Paused => 'warning',
                        JobStatus::Rejected, JobStatus::Removed => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('أُنشئ')->dateTime('Y-m-d')->sortable(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Job $record) => $record->status === JobStatus::PendingReview)
                    ->requiresConfirmation()
                    ->action(function (Job $record) {
                        app(JobModerationService::class)->approve(auth('admin')->id(), $record);

                        Notification::make()->title('تم اعتماد الإعلان ونشره')->success()->send();
                    }),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Job $record) => $record->status === JobStatus::PendingReview)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')->label('سبب الرفض')->required()->maxLength(500),
                    ])
                    ->action(function (Job $record, array $data) {
                        app(JobModerationService::class)->reject(auth('admin')->id(), $record, $data['reason']);

                        Notification::make()->title('تم رفض الإعلان')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
