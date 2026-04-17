<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

Route::middleware(['auth', 'verified','admin'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('/products', ProductController::class)->except(['show']);
    Route::resource('/customers', CustomerController::class);
    Route::resource('/suppliers', SupplierController::class);

    Route::get('/purchases/export/csv', [PurchaseController::class, 'exportCsv'])->name('purchases.export.csv');
    Route::resource('/purchases', PurchaseController::class);

    Route::resource('purchase-returns', PurchaseReturnController::class);
    Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])
        ->name('purchase-returns.approve');
    Route::get('purchase-returns-export', [PurchaseReturnController::class, 'exportCsv'])
        ->name('purchase-returns.export');
    Route::resource('stocks', StockController::class);

    Route::resource('sales', SaleController::class);
    Route::get('sales-export', [SaleController::class, 'exportCsv'])->name('sales.export');

    Route::resource('sale-returns', SaleReturnController::class);
    Route::post('sale-returns/{saleReturn}/approve', [SaleReturnController::class, 'approve'])->name('sale-returns.approve');
    Route::get('sale-returns-export', [SaleReturnController::class, 'exportCsv'])->name('sale-returns.export');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
