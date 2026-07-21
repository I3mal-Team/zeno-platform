<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Cities\Schemas;

use App\Models\City;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('الكود')->required()->maxLength(40)
                ->unique(ignoreRecord: true)
                ->disabled(fn (?City $record) => $record !== null)
                ->dehydrated(),
            TextInput::make('name.ar')->label('الاسم')->required()->maxLength(60),
            TextInput::make('region')->label('المنطقة')->maxLength(60),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('مفعّلة')->default(true),
        ]);
    }
}
