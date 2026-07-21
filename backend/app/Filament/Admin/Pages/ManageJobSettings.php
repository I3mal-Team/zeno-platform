<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\JobSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageJobSettings extends SettingsPage
{
    protected static string $settings = JobSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static UnitEnum|string|null $navigationGroup = 'الإعدادات';

    protected static ?string $title = 'إعدادات الإعلانات';

    protected static ?int $navigationSort = 2;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('default_duration_days')
                ->label('مدة الإعلان الافتراضية (أيام)')
                ->numeric()->required()->minValue(1)->maxValue(365),
            TextInput::make('max_duration_days')
                ->label('الحد الأقصى لمدة الإعلان (أيام)')
                ->numeric()->required()->minValue(1)->maxValue(365),
            TextInput::make('expiry_warning_days')
                ->label('التنبيه قبل الانتهاء بـ (أيام)')
                ->numeric()->required()->minValue(1)->maxValue(30),
        ]);
    }
}
