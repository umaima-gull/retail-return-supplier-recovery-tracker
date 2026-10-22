<?php

use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\DamagedProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpiryRecordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierClaimController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('customer-returns', CustomerReturnController::class)->except('show');
Route::resource('damaged-products', DamagedProductController::class)
    ->parameters(['damaged-products' => 'damaged'])
    ->except('show');
Route::resource('expiry', ExpiryRecordController::class)->except('show');
Route::resource('products', ProductController::class)->except('show');
Route::resource('suppliers', SupplierController::class)->except('show');

Route::resource('supplier-returns', SupplierClaimController::class)
    ->parameters(['supplier-returns' => 'claim'])
    ->only(['index', 'create', 'store', 'show', 'destroy']);
Route::put('supplier-returns/{claim}/status', [SupplierClaimController::class, 'updateStatus'])
    ->name('supplier-returns.status');

// Language switch: /lang/en  or  /lang/ur
Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'ur'], true), 404);
    session(['locale' => $locale]);

    return back();
})->name('lang.switch');