<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Reports\Tables;

use App\Enums\ReportStatus;
use App\Enums\ReportTarget;
use App\Models\Job;
use App\Models\Message;
use App\Models\Organization;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('target_type')->label('النوع')->badge()
                    ->formatStateUsing(fn (ReportTarget $state) => $state->label()),
                TextColumn::make('target_label')->label('المُبلَّغ عنه')
                    ->state(fn (Report $record) => self::describeTarget($record))
                    ->limit(40),
                TextColumn::make('reason.name')->label('السبب'),
                TextColumn::make('details')->label('التفاصيل')->limit(40)->toggleable(),
                TextColumn::make('reporter.phone_e164')->label('المُبلِّغ')->toggleable(),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (ReportStatus $state) => $state->label())
                    ->color(fn (ReportStatus $state) => $state->badgeColor()),
                TextColumn::make('created_at')->label('وصل')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('بدء المراجعة')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (Report $record) => $record->status === ReportStatus::New)
                    ->action(function (Report $record) {
                        $record->forceFill(['status' => ReportStatus::Reviewing->value])->save();

                        Notification::make()->title('البلاغ قيد المراجعة')->success()->send();
                    }),

                Action::make('resolve')
                    ->label('معالجة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Report $record) => $record->status->isOpen())
                    ->schema([
                        Textarea::make('note')->label('ما الذي تم')->required()->maxLength(500),
                    ])
                    ->action(fn (Report $record, array $data) => self::close($record, ReportStatus::Resolved, $data['note'])),

                Action::make('dismiss')
                    ->label('رفض البلاغ')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Report $record) => $record->status->isOpen())
                    ->schema([
                        Textarea::make('note')->label('سبب الرفض')->required()->maxLength(500),
                    ])
                    ->action(fn (Report $record, array $data) => self::close($record, ReportStatus::Dismissed, $data['note'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function close(Report $report, ReportStatus $status, string $note): void
    {
        $report->forceFill([
            'status' => $status->value,
            'resolved_by_admin_id' => auth('admin')->id(),
            'resolution_note' => $note,
            'resolved_at' => now(),
        ])->save();

        Notification::make()->title('تم إغلاق البلاغ')->success()->send();
    }

    /** A readable handle for whatever was reported, or a note that it is gone. */
    private static function describeTarget(Report $report): string
    {
        $target = $report->target();

        return match (true) {
            $target instanceof Job => $target->title,
            $target instanceof Organization => $target->name,
            $target instanceof User => $target->phone_e164,
            $target instanceof Message => (string) $target->body,
            default => 'حُذف',
        };
    }
}
