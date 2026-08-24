<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Jobs;

use App\Filament\Admin\Resources\Jobs\Pages\ListJobs;
use App\Filament\Admin\Resources\Jobs\Tables\JobsTable;
use App\Models\Job;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static UnitEnum|string|null $navigationGroup = 'المحتوى';

    protected static ?string $modelLabel = 'إعلان';

    protected static ?string $pluralModelLabel = 'الإعلانات';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function table(Table $table): Table
    {
        return JobsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Job::query()->where('status', 'pending_review')->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobs::route('/'),
        ];
    }
}
