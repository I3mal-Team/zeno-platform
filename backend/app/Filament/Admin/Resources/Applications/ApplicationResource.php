<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Applications;

use App\Filament\Admin\Resources\Applications\Pages\ListApplications;
use App\Filament\Admin\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static UnitEnum|string|null $navigationGroup = 'المحتوى';

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'التقديمات';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $new = Application::query()->where('status', 'submitted')->count();

        return $new > 0 ? (string) $new : null;
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
        ];
    }
}
