<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('products', ProductController::class)->except('show');
Route::resource('suppliers', SupplierController::class)->except('show');

// Language switch: /lang/en  or  /lang/ur
Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ur'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('lang.switch');