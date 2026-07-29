<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CandidateProfiles\Pages;

use App\Filament\Admin\Resources\CandidateProfiles\CandidateProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListCandidateProfiles extends ListRecords
{
    protected static string $resource = CandidateProfileResource::class;
}
