<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Districts\Pages;

use App\Filament\Admin\Resources\Districts\DistrictResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDistrict extends CreateRecord
{
    protected static string $resource = DistrictResource::class;
}
