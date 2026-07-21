<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Catalog\CityController;
use Illuminate\Support\Facades\Route;

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('cities', [CityController::class, 'index'])->name('cities.index');
Route::get('cities/{city}/districts', [CityController::class, 'districts'])->name('cities.districts');
