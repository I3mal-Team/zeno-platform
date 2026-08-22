<?php

declare(strict_types=1);

use App\Http\Controllers\Site\CompanyShowController;
use App\Http\Controllers\Site\Contact\ContactController;
use App\Http\Controllers\Site\Marketing\AboutController;
use App\Http\Controllers\Site\Marketing\HomeController;
use App\Http\Controllers\Site\Marketing\PricingController;
use App\Http\Controllers\Site\Marketing\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('about', AboutController::class)->name('about');
Route::get('pricing', PricingController::class)->name('pricing');
Route::get('terms', TermsController::class)->name('terms');

Route::get('companies/{slug}', CompanyShowController::class)->name('companies.show');

Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact', [ContactController::class, 'send'])
    ->middleware('throttle:contact')
    ->name('contact.send');
