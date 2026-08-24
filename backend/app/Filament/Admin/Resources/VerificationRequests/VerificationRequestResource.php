<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VerificationRequests;

use App\Enums\VerificationRequestStatus;
use App\Filament\Admin\Resources\VerificationRequests\Pages\ListVerificationRequests;
use App\Filament\Admin\Resources\VerificationRequests\Tables\VerificationRequestsTable;
use App\Models\VerificationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VerificationRequestResource extends Resource
{
    protected static ?string $model = VerificationRequest::class;

    protected static UnitEnum|string|null $navigationGroup = 'الإشراف';

    protected static ?string $modelLabel = 'طلب توثيق';

    protected static ?string $pluralModelLabel = 'طلبات التوثيق';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    public static function table(Table $table): Table
    {
        return VerificationRequestsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = VerificationRequest::query()->where('status', VerificationRequestStatus::Pending->value)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => ListVerificationRequests::route('/'),
        ];
    }
}
