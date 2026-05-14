<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\ClothSewingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueManagementController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalesManController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    Route::resource('/products', ProductController::class)->except(['show'])->middleware('permission:manage products');
    Route::get('/products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode')->middleware('permission:manage products');
    Route::get('/customers/{customer}/transactions/export', [CustomerController::class, 'exportTransactions'])->name('customers.transactions.export')->middleware('permission:manage customers|manage dues');
    Route::resource('/customers', CustomerController::class)->middleware('permission:manage customers');
    Route::get('/suppliers/{supplier}/transactions/export', [SupplierController::class, 'exportTransactions'])->name('suppliers.transactions.export')->middleware('permission:manage suppliers|manage dues');
    Route::resource('/suppliers', SupplierController::class)->middleware('permission:manage suppliers');
    Route::resource('/cloth-sewings', ClothSewingController::class)
        ->parameters(['cloth-sewings' => 'clothSewing'])
        ->except(['show']);
    Route::resource('/sales-men', SalesManController::class)
        ->parameters(['sales-men' => 'salesMan']);

    Route::resource('/users', UserController::class)->except(['show'])->middleware('permission:manage users');
    Route::resource('/roles', RoleController::class)->except(['show'])->middleware('permission:manage roles');
    Route::resource('/permissions', PermissionController::class)->except(['show'])->middleware('permission:manage permissions');
    Route::resource('/shops', ShopController::class)->middleware('permission:manage shops');
    Route::get('/shops/{shop}/executives', [ShopController::class, 'executives'])->name('shops.executives')->middleware('permission:manage shops');
    Route::post('/shops/{shop}/executives', [ShopController::class, 'syncExecutives'])->name('shops.executives.sync')->middleware('permission:manage shops');

    Route::get('/purchases/export/csv', [PurchaseController::class, 'exportCsv'])->name('purchases.export.csv')->middleware('permission:manage purchases');
    Route::resource('/purchases', PurchaseController::class)->middleware('permission:manage purchases');

    Route::resource('purchase-returns', PurchaseReturnController::class)->middleware('permission:manage purchase returns');
    Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])
        ->name('purchase-returns.approve')->middleware('permission:manage purchase returns');
    Route::get('purchase-returns-export', [PurchaseReturnController::class, 'exportCsv'])
        ->name('purchase-returns.export')->middleware('permission:manage purchase returns');
    Route::get('stocks/distribute/create', [StockController::class, 'distribute'])->name('stocks.distribute')->middleware('permission:distribute stock');
    Route::post('stocks/distribute', [StockController::class, 'storeDistribution'])->name('stocks.distribute.store')->middleware('permission:distribute stock');
    Route::resource('stocks', StockController::class)->middleware('permission:manage stock');

    Route::get('sales-export', [SaleController::class, 'exportCsv'])->name('sales.export')->middleware('permission:manage sales');
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice')->middleware('permission:manage sales');
    Route::resource('sales', SaleController::class)->middleware('permission:manage sales');

    Route::resource('sale-returns', SaleReturnController::class)->middleware('permission:manage sale returns');
    Route::post('sale-returns/{saleReturn}/approve', [SaleReturnController::class, 'approve'])->name('sale-returns.approve')->middleware('permission:manage sale returns');
    Route::get('sale-returns-export', [SaleReturnController::class, 'exportCsv'])->name('sale-returns.export')->middleware('permission:manage sale returns');

    Route::get('expenses-export', [ExpenseController::class, 'exportCsv'])->name('expenses.export')->middleware('permission:manage expenses');
    Route::resource('expenses', ExpenseController::class)->middleware('permission:manage expenses');

    Route::resource('cash-transactions', CashTransactionController::class)
        ->parameters(['cash-transactions' => 'cashTransaction'])
        ->except(['show'])
        ->middleware('permission:manage cash');
    Route::get('dues', [DueManagementController::class, 'index'])->name('dues.index')->middleware('permission:manage dues');
    Route::get('dues/customer-wise', [DueManagementController::class, 'customer'])->name('dues.customer')->middleware('permission:manage dues');
    Route::get('dues/customer-wise/export', [DueManagementController::class, 'exportCustomer'])->name('dues.customer.export')->middleware('permission:manage dues');
    Route::get('dues/customers/{customer}/transactions', [CustomerController::class, 'show'])->name('dues.customers.transactions')->middleware('permission:manage dues');
    Route::get('dues/supplier-wise', [DueManagementController::class, 'supplier'])->name('dues.supplier')->middleware('permission:manage dues');
    Route::get('dues/supplier-wise/export', [DueManagementController::class, 'exportSupplier'])->name('dues.supplier.export')->middleware('permission:manage dues');
    Route::get('dues/sale-wise', [DueManagementController::class, 'sale'])->name('dues.sale')->middleware('permission:manage dues');
    Route::get('dues/sale-wise/export', [DueManagementController::class, 'exportSale'])->name('dues.sale.export')->middleware('permission:manage dues');
    Route::get('dues/purchase-wise', [DueManagementController::class, 'purchase'])->name('dues.purchase')->middleware('permission:manage dues');
    Route::get('dues/purchase-wise/export', [DueManagementController::class, 'exportPurchase'])->name('dues.purchase.export')->middleware('permission:manage dues');
    Route::get('dues/manual', [DueManagementController::class, 'manual'])->name('dues.manual')->middleware('permission:manage dues');
    Route::get('dues/manual/export', [DueManagementController::class, 'exportManual'])->name('dues.manual.export')->middleware('permission:manage dues');
    Route::post('dues', [DueManagementController::class, 'store'])->name('dues.store')->middleware('permission:manage dues');
    Route::get('dues/{manualDue}/edit', [DueManagementController::class, 'edit'])->name('dues.edit')->middleware('permission:manage dues');
    Route::put('dues/{manualDue}', [DueManagementController::class, 'update'])->name('dues.update')->middleware('permission:manage dues');
    Route::delete('dues/{manualDue}', [DueManagementController::class, 'destroy'])->name('dues.destroy')->middleware('permission:manage dues');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
