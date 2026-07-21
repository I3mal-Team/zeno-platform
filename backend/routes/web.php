<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::name('site.')->group(function () {
    require __DIR__.'/web/marketing.php';
    require __DIR__.'/web/jobs.php';
});

require __DIR__.'/web/employer.php';
