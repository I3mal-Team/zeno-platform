<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContactRequests\Tables;

use App\Models\ContactRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->weight('bold'),
                TextColumn::make('email')->label('البريد')->searchable()->copyable(),
                TextColumn::make('topic')->label('الموضوع')->badge()->toggleable(),
                TextColumn::make('message')->label('الرسالة')->limit(50)->tooltip(fn (ContactRequest $r) => $r->message),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'handled' ? 'تمّت المعالجة' : 'جديدة')
                    ->color(fn (string $state): string => $state === 'handled' ? 'success' : 'warning'),
                TextColumn::make('created_at')->label('وصلت')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->recordActions([
                Action::make('handle')
                    ->label('تمّت المعالجة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContactRequest $record): bool => $record->status !== 'handled')
                    ->requiresConfirmation()
                    ->action(function (ContactRequest $record): void {
                        $record->forceFill([
                            'status' => 'handled',
                            'handled_by_admin_id' => auth('admin')->id(),
                            'handled_at' => now(),
                        ])->save();

                        Notification::make()->title('تم وضع الرسالة كمُعالَجة')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
