<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\OtpSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageOtpSettings extends SettingsPage
{
    protected static string $settings = OtpSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static UnitEnum|string|null $navigationGroup = 'الإعدادات';

    protected static ?string $title = 'إعدادات رمز التحقق';

    protected static ?int $navigationSort = 1;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('expiry_minutes')
                ->label('صلاحية الرمز (دقائق)')
                ->numeric()->required()->minValue(1)->maxValue(30),
            TextInput::make('max_attempts')
                ->label('عدد المحاولات لكل رمز')
                ->numeric()->required()->minValue(1)->maxValue(10),
            TextInput::make('resend_cooldown_seconds')
                ->label('مهلة إعادة الإرسال (ثواني)')
                ->numeric()->required()->minValue(15)->maxValue(600),
            TextInput::make('max_requests_per_window')
                ->label('عدد الرموز لكل رقم في النافذة')
                ->numeric()->required()->minValue(1)->maxValue(20),
            TextInput::make('window_minutes')
                ->label('طول النافذة (دقائق)')
                ->numeric()->required()->minValue(5)->maxValue(1440),
        ]);
    }
}
