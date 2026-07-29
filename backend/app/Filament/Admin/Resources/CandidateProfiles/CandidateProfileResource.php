<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CandidateProfiles;

use App\Filament\Admin\Resources\CandidateProfiles\Pages\ListCandidateProfiles;
use App\Filament\Admin\Resources\CandidateProfiles\Tables\CandidateProfilesTable;
use App\Models\CandidateProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CandidateProfileResource extends Resource
{
    protected static ?string $model = CandidateProfile::class;

    protected static UnitEnum|string|null $navigationGroup = 'المستخدمون';

    protected static ?string $modelLabel = 'باحث عن عمل';

    protected static ?string $pluralModelLabel = 'الباحثون عن عمل';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function table(Table $table): Table
    {
        return CandidateProfilesTable::configure($table);
    }

    /** @return array<string, mixed> */
    public static function getPages(): array
    {
        return [
            'index' => ListCandidateProfiles::route('/'),
        ];
    }
}
