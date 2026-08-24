<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Reports;

use App\Filament\Admin\Resources\Reports\Pages\ListReports;
use App\Filament\Admin\Resources\Reports\Tables\ReportsTable;
use App\Models\Report;
use App\Repositories\ReportRepository;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static UnitEnum|string|null $navigationGroup = 'الإشراف';

    protected static ?string $modelLabel = 'بلاغ';

    protected static ?string $pluralModelLabel = 'البلاغات';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function table(Table $table): Table
    {
        return ReportsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $open = app(ReportRepository::class)->countOpen();

        return $open > 0 ? (string) $open : null;
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
        ];
    }
}
