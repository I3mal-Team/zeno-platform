<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\IntegrationServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    IntegrationServiceProvider::class,
];
