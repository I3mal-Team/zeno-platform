<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('الكود')
                ->required()
                ->maxLength(30)
                ->unique(ignoreRecord: true)
                // Application logic branches on the code, so an existing one is
                // never editable.
                ->disabled(fn (?Category $record) => $record !== null)
                ->dehydrated(),
            TextInput::make('name.ar')->label('الاسم')->required()->maxLength(60),
            TextInput::make('icon')->label('الأيقونة')->required()->maxLength(50),
            TextInput::make('color_token')->label('لون العرض')->required()->maxLength(30),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Toggle::make('is_active')->label('مفعّل')->default(true),
        ]);
    }
}
