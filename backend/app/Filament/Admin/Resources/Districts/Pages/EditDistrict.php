<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Districts\Pages;

use App\Filament\Admin\Resources\Districts\DistrictResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDistrict extends EditRecord
{
    protected static string $resource = DistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
