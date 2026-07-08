<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\ClothSewingController;
use App\Http\Controllers\CarryManController;
use App\Http\Controllers\CarryManWorkLogController;
use App\Http\Controllers\ComputerManController;
use App\Http\Controllers\ComputerManWorkLogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DueManagementController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GareyManController;
use App\Http\Controllers\GareyManWorkLogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReceivedClothController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalaryAdvanceController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TailorController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    $crudResource = function (string $uri, string $controller, string $permissionBase, array $options = []) {
        $parameters = $options['parameters'] ?? [];
        $except = $options['except'] ?? [];
        $viewActions = array_values(array_diff(['index', 'show'], $except));

        Route::resource($uri, $controller)
            ->parameters($parameters)
            ->only(['create', 'store'])
            ->middleware("permission:manage {$permissionBase}|create {$permissionBase}");

        if ($viewActions) {
            Route::resource($uri, $controller)
                ->parameters($parameters)
                ->only($viewActions)
                ->middleware("permission:manage {$permissionBase}|view {$permissionBase}");
        }

        Route::resource($uri, $controller)
            ->parameters($parameters)
            ->only(['edit', 'update'])
            ->middleware("permission:manage {$permissionBase}|edit {$permissionBase}");
        Route::resource($uri, $controller)
            ->parameters($parameters)
            ->only(['destroy'])
            ->middleware("permission:manage {$permissionBase}|delete {$permissionBase}");
    };

    $crudResource('/products', ProductController::class, 'products', ['except' => ['show']]);
    Route::get('/products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode')->middleware('permission:manage products|view products');
    Route::get('/customers/suggestions', [CustomerController::class, 'suggestions'])->name('customers.suggestions')->middleware('permission:manage customers|view customers');
    Route::get('/customers/export/pdf', [CustomerController::class, 'exportIndexPdf'])->name('customers.export.pdf')->middleware('permission:manage customers|view customers');
    Route::get('/customers/{customer}/transactions/export', [CustomerController::class, 'exportTransactions'])->name('customers.transactions.export')->middleware('permission:manage customers|view customers|manage dues|view dues');
    $crudResource('/customers', CustomerController::class, 'customers');
    Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportIndexPdf'])->name('suppliers.export.pdf')->middleware('permission:manage suppliers|view suppliers');
    Route::get('/suppliers/{supplier}/transactions/export', [SupplierController::class, 'exportTransactions'])->name('suppliers.transactions.export')->middleware('permission:manage suppliers|view suppliers|manage dues|view dues');
    $crudResource('/suppliers', SupplierController::class, 'suppliers');
    Route::get('/cloth-sewings/tailors/{tailor}/receive', [ClothSewingController::class, 'receiveData'])->name('cloth-sewings.tailors.receive')->middleware('permission:manage cloth sewings|view cloth sewings');
    Route::post('/cloth-sewings/tailors/{tailor}/receive', [ClothSewingController::class, 'saveReceived'])->name('cloth-sewings.tailors.receive.save')->middleware('permission:manage cloth sewings|edit cloth sewings|create received cloths');
    Route::get('/cloth-sewings/tailors/{tailor}/logs', [ClothSewingController::class, 'logs'])->name('cloth-sewings.tailors.logs')->middleware('permission:manage cloth sewings|view cloth sewings');
    Route::get('/cloth-sewings/tailors/{tailor}/logs/export', [ClothSewingController::class, 'exportLogs'])->name('cloth-sewings.tailors.logs.export')->middleware('permission:manage cloth sewings|view cloth sewings');
    $crudResource('/cloth-sewings', ClothSewingController::class, 'cloth sewings', ['parameters' => ['cloth-sewings' => 'clothSewing'], 'except' => ['show']]);
    Route::resource('/tailors', TailorController::class)->only(['create', 'store'])
        ->middleware('permission:manage cloth sewings|create cloth sewings');
    Route::resource('/tailors', TailorController::class)->only(['index', 'show'])
        ->whereNumber('tailor')
        ->middleware('permission:manage cloth sewings|view cloth sewings');
    Route::resource('/tailors', TailorController::class)->only(['edit', 'update'])
        ->whereNumber('tailor')
        ->middleware('permission:manage cloth sewings|edit cloth sewings');
    Route::resource('/tailors', TailorController::class)->only(['destroy'])
        ->whereNumber('tailor')
        ->middleware('permission:manage cloth sewings|delete cloth sewings');
    $crudResource('/received-cloths', ReceivedClothController::class, 'received cloths', ['parameters' => ['received-cloths' => 'receivedCloth'], 'except' => ['show']]);
    $crudResource('/carry-men', CarryManController::class, 'carry men');
    Route::post('/carry-man-work-logs/{carryManWorkLog}/receive', [CarryManWorkLogController::class, 'receive'])->name('carry-man-work-logs.receive')->middleware('permission:manage carry man work logs|edit carry man work logs');
    $crudResource('/carry-man-work-logs', CarryManWorkLogController::class, 'carry man work logs', ['except' => ['show']]);
    $crudResource('/computer-men', ComputerManController::class, 'computer men');
    Route::post('/computer-man-work-logs/{computerManWorkLog}/receive', [ComputerManWorkLogController::class, 'receive'])->name('computer-man-work-logs.receive')->middleware('permission:manage computer man work logs|edit computer man work logs');
    $crudResource('/computer-man-work-logs', ComputerManWorkLogController::class, 'computer man work logs', ['except' => ['show']]);
    $crudResource('/garey-men', GareyManController::class, 'garey men');
    Route::post('/garey-man-work-logs/{gareyManWorkLog}/receive', [GareyManWorkLogController::class, 'receive'])->name('garey-man-work-logs.receive')->middleware('permission:manage garey man work logs|edit garey man work logs');
    $crudResource('/garey-man-work-logs', GareyManWorkLogController::class, 'garey man work logs', ['except' => ['show']]);
    $crudResource('/users', UserController::class, 'users', ['except' => ['show']]);
    $crudResource('/roles', RoleController::class, 'roles', ['except' => ['show']]);
    $crudResource('/permissions', PermissionController::class, 'permissions', ['except' => ['show']]);
    $crudResource('/shops', ShopController::class, 'shops');
    Route::get('/shops/{shop}/executives', [ShopController::class, 'executives'])->name('shops.executives')->middleware('permission:manage shops|edit shops');
    Route::post('/shops/{shop}/executives', [ShopController::class, 'syncExecutives'])->name('shops.executives.sync')->middleware('permission:manage shops|edit shops');

    Route::get('/purchases/export/csv', [PurchaseController::class, 'exportCsv'])->name('purchases.export.csv')->middleware('permission:manage purchases|view purchases');
    $crudResource('/purchases', PurchaseController::class, 'purchases');

    $crudResource('purchase-returns', PurchaseReturnController::class, 'purchase returns');
    Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])
        ->name('purchase-returns.approve')->middleware('permission:manage purchase returns|approve purchase returns');
    Route::get('purchase-returns-export', [PurchaseReturnController::class, 'exportCsv'])
        ->name('purchase-returns.export')->middleware('permission:manage purchase returns|view purchase returns');
    Route::get('stocks/distribute/create', [StockController::class, 'distribute'])->name('stocks.distribute')->middleware('permission:distribute stock');
    Route::post('stocks/distribute', [StockController::class, 'storeDistribution'])->name('stocks.distribute.store')->middleware('permission:distribute stock');
    Route::get('stocks/adjustments', [StockController::class, 'adjustments'])->name('stocks.adjustments')->middleware('permission:manage stock|edit stock');
    Route::post('stocks/adjustments', [StockController::class, 'storeAdjustment'])->name('stocks.adjustments.store')->middleware('permission:manage stock|edit stock');
    Route::post('stocks/transfers', [StockController::class, 'storeTransfer'])->name('stocks.transfers.store')->middleware('permission:manage stock|edit stock');
    Route::get('stocks/distributions/pending', [StockController::class, 'pendingDistributions'])->name('stocks.distributions.pending')->middleware('permission:view stock distributions|receive stock distributions');
    Route::post('stocks/distributions/{distribution}/receive', [StockController::class, 'receiveDistribution'])->name('stocks.distributions.receive')->middleware('permission:receive stock distributions');
    Route::post('stocks/distributions/{distribution}/cancel', [StockController::class, 'cancelDistribution'])->name('stocks.distributions.cancel')->middleware('permission:receive stock distributions');
    Route::get('stocks/export/pdf', [StockController::class, 'exportPdf'])->name('stocks.export.pdf')->middleware('permission:manage stock|view stock');
    $crudResource('stocks', StockController::class, 'stock');

    Route::get('sales-export', [SaleController::class, 'exportCsv'])->name('sales.export')->middleware('permission:manage sales|view sales');
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice')->middleware('permission:manage sales|view sales');
    $crudResource('sales', SaleController::class, 'sales');

    $crudResource('sale-returns', SaleReturnController::class, 'sale returns');
    Route::post('sale-returns/{saleReturn}/approve', [SaleReturnController::class, 'approve'])->name('sale-returns.approve')->middleware('permission:manage sale returns|approve sale returns');
    Route::get('sale-returns-export', [SaleReturnController::class, 'exportCsv'])->name('sale-returns.export')->middleware('permission:manage sale returns|view sale returns');

    Route::get('expenses-export', [ExpenseController::class, 'exportCsv'])->name('expenses.export')->middleware('permission:manage expenses|view expenses');
    $crudResource('expenses', ExpenseController::class, 'expenses');

    Route::resource('employees', EmployeeController::class)
        ->only(['create', 'store'])
        ->middleware('permission:manage employees|create employees');
    Route::resource('employees', EmployeeController::class)
        ->only(['edit', 'update'])
        ->whereNumber('employee')
        ->middleware('permission:manage employees|edit employees');
    Route::resource('employees', EmployeeController::class)
        ->only(['index', 'show'])
        ->whereNumber('employee')
        ->middleware('permission:manage employees|view employees');
    Route::get('salaries', [SalaryController::class, 'index'])
        ->name('salaries.index')
        ->middleware('permission:manage salaries|view salaries|create salaries');
    Route::post('salaries', [SalaryController::class, 'store'])
        ->name('salaries.store')
        ->middleware('permission:manage salaries|create salaries');
    Route::resource('salary-advances', SalaryAdvanceController::class)
        ->only(['create', 'store'])
        ->middleware('permission:manage salary advances|create salary advances');
    Route::resource('salary-advances', SalaryAdvanceController::class)
        ->only(['edit', 'update'])
        ->whereNumber('salary_advance')
        ->middleware('permission:manage salary advances|edit salary advances');
    Route::resource('salary-advances', SalaryAdvanceController::class)
        ->only(['index', 'destroy'])
        ->whereNumber('salary_advance')
        ->middleware('permission:manage salary advances|view salary advances|delete salary advances');

    $crudResource('cash-transactions', CashTransactionController::class, 'cash', ['parameters' => ['cash-transactions' => 'cashTransaction'], 'except' => ['show']]);
    $crudResource('cheques', ChequeController::class, 'cheques');
    Route::get('dues', [DueManagementController::class, 'index'])->name('dues.index')->middleware('permission:manage dues|view dues');
    Route::get('dues/customer-wise', [DueManagementController::class, 'customer'])->name('dues.customer')->middleware('permission:manage dues|view dues');
    Route::get('dues/customer-wise/export', [DueManagementController::class, 'exportCustomer'])->name('dues.customer.export')->middleware('permission:manage dues|view dues');
    Route::get('dues/customers/{customer}/transactions', [CustomerController::class, 'show'])->name('dues.customers.transactions')->middleware('permission:manage dues|view dues');
    Route::get('dues/supplier-wise', [DueManagementController::class, 'supplier'])->name('dues.supplier')->middleware('permission:manage dues|view dues');
    Route::get('dues/supplier-wise/export', [DueManagementController::class, 'exportSupplier'])->name('dues.supplier.export')->middleware('permission:manage dues|view dues');
    Route::get('dues/sale-wise', [DueManagementController::class, 'sale'])->name('dues.sale')->middleware('permission:manage dues|view dues');
    Route::get('dues/sale-wise/export', [DueManagementController::class, 'exportSale'])->name('dues.sale.export')->middleware('permission:manage dues|view dues');
    Route::get('dues/purchase-wise', [DueManagementController::class, 'purchase'])->name('dues.purchase')->middleware('permission:manage dues|view dues');
    Route::get('dues/purchase-wise/export', [DueManagementController::class, 'exportPurchase'])->name('dues.purchase.export')->middleware('permission:manage dues|view dues');
    Route::get('dues/manual', [DueManagementController::class, 'manual'])->name('dues.manual')->middleware('permission:manage dues|view dues|create dues');
    Route::get('dues/manual/export', [DueManagementController::class, 'exportManual'])->name('dues.manual.export')->middleware('permission:manage dues|view dues');
    Route::post('dues', [DueManagementController::class, 'store'])->name('dues.store')->middleware('permission:manage dues|create dues');
    Route::get('dues/{manualDue}/edit', [DueManagementController::class, 'edit'])->name('dues.edit')->middleware('permission:manage dues|edit dues');
    Route::put('dues/{manualDue}', [DueManagementController::class, 'update'])->name('dues.update')->middleware('permission:manage dues|edit dues');
    Route::delete('dues/{manualDue}', [DueManagementController::class, 'destroy'])->name('dues.destroy')->middleware('permission:manage dues|delete dues');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
