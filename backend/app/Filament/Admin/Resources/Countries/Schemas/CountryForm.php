<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('iso2')->label('رمز الدولة (ISO2)')->required()->length(2)
                ->unique(ignoreRecord: true)
                ->helperText('مثال: SA, EG')
                ->extraInputAttributes(['style' => 'text-transform:uppercase']),
            TextInput::make('dial_code')->label('مفتاح الاتصال')->required()->maxLength(6)
                ->helperText('مثال: ‎+966'),
            TextInput::make('name')->label('الاسم')->required()->maxLength(80),
            TextInput::make('flag')->label('العلم (إيموجي)')->required()->maxLength(8),
            TextInput::make('placeholder')->label('مثال الرقم')->maxLength(24)
                ->helperText('يظهر داخل حقل الجوال · مثال: ‎5X XXX XXXX'),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('مفعّلة')->default(true),
        ]);
    }
}
