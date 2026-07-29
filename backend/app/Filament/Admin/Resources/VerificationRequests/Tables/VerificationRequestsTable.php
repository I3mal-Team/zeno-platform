<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VerificationRequests\Tables;

use App\Enums\VerificationRequestStatus;
use App\Models\VerificationRequest;
use App\Services\VerificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VerificationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization.name')->label('المنشأة')->searchable(),
                TextColumn::make('organization.type')->label('النوع')
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('submittedBy.phone_e164')->label('مقدّم الطلب'),
                TextColumn::make('documents_count')->label('المستندات')
                    ->state(fn (VerificationRequest $record) => $record->getMedia(VerificationRequest::DOCUMENTS_COLLECTION)->count()),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (VerificationRequestStatus $state) => $state->label())
                    ->color(fn (VerificationRequestStatus $state) => $state->badgeColor()),
                TextColumn::make('created_at')->label('أُرسل')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->recordActions([
                // Opens each uploaded document in a new tab; media is served
                // through the library's signed URLs, not a public path.
                Action::make('documents')
                    ->label('المستندات')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->modalHeading('مستندات التوثيق')
                    ->modalSubmitAction(false)
                    ->modalContent(fn (VerificationRequest $record) => view('filament.admin.verification-documents', [
                        'documents' => $record->getMedia(VerificationRequest::DOCUMENTS_COLLECTION),
                    ])),

                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (VerificationRequest $record) => $record->status === VerificationRequestStatus::Pending)
                    ->requiresConfirmation()
                    ->modalDescription('سيصبح بإمكان المنشأة نشر إعلاناتها فوراً بدون مراجعة.')
                    ->action(function (VerificationRequest $record) {
                        app(VerificationService::class)->decide(
                            $record,
                            VerificationRequestStatus::Approved,
                            auth('admin')->id(),
                        );

                        Notification::make()->title('تم توثيق المنشأة')->success()->send();
                    }),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (VerificationRequest $record) => $record->status === VerificationRequestStatus::Pending)
                    ->schema([
                        Textarea::make('note')->label('سبب الرفض')->required()->maxLength(500)
                            ->helperText('يظهر لصاحب العمل ليصحّح مستنداته.'),
                    ])
                    ->action(function (VerificationRequest $record, array $data) {
                        app(VerificationService::class)->decide(
                            $record,
                            VerificationRequestStatus::Rejected,
                            auth('admin')->id(),
                            $data['note'],
                        );

                        Notification::make()->title('تم رفض الطلب')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
