<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Models\User;
use App\Services\AdminUserService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_e164')->label('الجوال')->searchable(),
                TextColumn::make('role')->label('الدور')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'candidate' => 'باحث عن عمل',
                        'employer' => 'صاحب عمل',
                        default => $state,
                    }),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->color(fn (string $state) => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'نشط',
                        'suspended' => 'موقوف',
                        default => 'محذوف',
                    }),
                TextColumn::make('created_at')->label('التسجيل')->dateTime('Y-m-d')->sortable(),
                TextColumn::make('last_active_at')->label('آخر نشاط')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->label('الدور')->options([
                    'candidate' => 'باحث عن عمل',
                    'employer' => 'صاحب عمل',
                ]),
                SelectFilter::make('status')->label('الحالة')->options([
                    'active' => 'نشط',
                    'suspended' => 'موقوف',
                ]),
            ])
            ->recordActions([
                Action::make('suspend')
                    ->label('إيقاف')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    // The reason is mandatory here and enforced again by a NOT
                    // NULL column, so no account is ever suspended silently.
                    ->schema([
                        Textarea::make('reason')->label('السبب')->required()->maxLength(500),
                    ])
                    ->action(function (User $record, array $data) {
                        app(AdminUserService::class)->suspend($record, $data['reason'], auth('admin')->id());

                        Notification::make()->title('تم إيقاف الحساب')->success()->send();
                    }),

                Action::make('reinstate')
                    ->label('إعادة تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status === 'suspended')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')->label('السبب')->required()->maxLength(500),
                    ])
                    ->action(function (User $record, array $data) {
                        app(AdminUserService::class)->reinstate($record, $data['reason'], auth('admin')->id());

                        Notification::make()->title('تم إعادة تفعيل الحساب')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
