<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ContactRequests;

use App\Filament\Admin\Resources\ContactRequests\Pages\ListContactRequests;
use App\Filament\Admin\Resources\ContactRequests\Tables\ContactRequestsTable;
use App\Models\ContactRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactRequestResource extends Resource
{
    protected static ?string $model = ContactRequest::class;

    protected static UnitEnum|string|null $navigationGroup = 'الإشراف';

    protected static ?string $modelLabel = 'رسالة تواصل';

    protected static ?string $pluralModelLabel = 'طلبات التواصل';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function table(Table $table): Table
    {
        return ContactRequestsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $new = ContactRequest::query()->where('status', 'new')->count();

        return $new > 0 ? (string) $new : null;
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => ListContactRequests::route('/'),
        ];
    }
}
