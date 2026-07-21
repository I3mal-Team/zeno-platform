<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Catalog\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
