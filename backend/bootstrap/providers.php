<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\EmployerPanelProvider;
use App\Providers\IntegrationServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    EmployerPanelProvider::class,
    IntegrationServiceProvider::class,
];
