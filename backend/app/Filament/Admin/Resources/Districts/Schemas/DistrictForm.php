<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Districts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('city_id')->label('المدينة')
                ->relationship('city', 'code')->required()->searchable(),
            TextInput::make('code')->label('الكود')->required()->maxLength(60),
            TextInput::make('name.ar')->label('الاسم')->required()->maxLength(80),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('مفعّل')->default(true),
        ]);
    }
}
