<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    require __DIR__.'/api/v1/auth.php';
    require __DIR__.'/api/v1/catalog.php';
});
