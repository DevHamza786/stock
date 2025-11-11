<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MineVendorController;
use App\Http\Controllers\ConditionStatusController;
use App\Http\Controllers\StockAdditionController;
use App\Http\Controllers\StockIssuedController;
use App\Http\Controllers\DailyProductionController;
use App\Http\Controllers\GatePassController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\BankPaymentVoucherController;
use App\Http\Controllers\CashPaymentVoucherController;
use App\Http\Controllers\PurchaseVoucherController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DatabaseViewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('stock-management.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes (Admin Only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('user-management')->name('user-management.')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/{user}/permissions', [UserController::class, 'editPermissions'])->name('users.permissions');
    Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
});

// Stock Management Routes
Route::middleware(['auth', 'verified'])->prefix('stock-management')->name('stock-management.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StockManagementController::class, 'dashboard'])->name('dashboard');
    Route::get('/stock-levels', [StockManagementController::class, 'getStockLevels'])->name('stock-levels');
    Route::get('/available-stock', [StockManagementController::class, 'getAvailableStock'])->name('available-stock');

    // Stock Additions - Excel routes (must come before resource routes)
    // Excel Edit routes - Admin only
    Route::get('stock-additions/edit-excel', [StockAdditionController::class, 'editExcel'])->middleware('admin')->name('stock-additions.edit-excel');
    Route::put('stock-additions/update-multiple', [StockAdditionController::class, 'updateMultiple'])->middleware('admin')->name('stock-additions.update-multiple');
    // Excel Add routes - Accessible to all authenticated users
    Route::post('stock-additions/store-multiple', [StockAdditionController::class, 'storeMultiple'])->name('stock-additions.store-multiple');
    Route::post('stock-additions/calculate-sqft', [StockAdditionController::class, 'calculateSqft'])->name('stock-additions.calculate-sqft');
    
    // Stock Additions - Resource routes (excluding edit and delete which are handled separately)
    Route::resource('stock-additions', StockAdditionController::class)->except(['edit', 'update', 'destroy']);
    
    // Protect edit and delete routes with permission middleware
    Route::get('stock-additions/{stockAddition}/edit', [StockAdditionController::class, 'edit'])->middleware('permission:edit,stock-additions')->name('stock-additions.edit');
    Route::put('stock-additions/{stockAddition}', [StockAdditionController::class, 'update'])->middleware('permission:edit,stock-additions')->name('stock-additions.update');
    Route::delete('stock-additions/{stockAddition}', [StockAdditionController::class, 'destroy'])->middleware('permission:delete,stock-additions')->name('stock-additions.destroy');

    // TEMPORARY TEST ROUTE
    Route::get('stock-additions/{stockAddition}/edit-test', function ($stockAdditionId) {
        $stockAddition = \App\Models\StockAddition::findOrFail($stockAdditionId);
        $products = \App\Models\Product::where('is_active', true)->get();
        $mineVendors = \App\Models\MineVendor::where('is_active', true)->get();
        $conditionStatuses = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();
        return view('stock-management.stock-additions.edit-test', compact('stockAddition', 'products', 'mineVendors', 'conditionStatuses'));
    })->name('stock-additions.edit-test');

    // Stock Issued - Resource routes (excluding edit and delete which are handled separately)
    Route::resource('stock-issued', StockIssuedController::class)->except(['edit', 'update', 'destroy']);
    Route::get('stock-issued/available-quantity/{stockAddition}', [StockIssuedController::class, 'getAvailableQuantity'])->name('stock-issued.available-quantity');
    
    // Protect edit and delete routes with permission middleware
    Route::get('stock-issued/{stockIssued}/edit', [StockIssuedController::class, 'edit'])->middleware('permission:edit,stock-issued')->name('stock-issued.edit');
    Route::put('stock-issued/{stockIssued}', [StockIssuedController::class, 'update'])->middleware('permission:edit,stock-issued')->name('stock-issued.update');
    Route::delete('stock-issued/{stockIssued}', [StockIssuedController::class, 'destroy'])->middleware('permission:delete,stock-issued')->name('stock-issued.destroy');

    // Daily Production
    // Daily Production - Excel routes (must come before resource routes)
    Route::get('daily-production/create-excel', [DailyProductionController::class, 'createExcel'])->name('daily-production.create-excel');
    Route::post('daily-production/store-multiple', [DailyProductionController::class, 'storeMultiple'])->name('daily-production.store-multiple');
    
    // Daily Production - Resource routes (excluding edit and delete which are handled separately)
    Route::resource('daily-production', DailyProductionController::class)->except(['edit', 'update', 'destroy']);
    Route::get('daily-production/{dailyProduction}/print', [DailyProductionController::class, 'print'])->name('daily-production.print');
    Route::patch('daily-production/{dailyProduction}/close', [DailyProductionController::class, 'close'])->name('daily-production.close');
    Route::patch('daily-production/{dailyProduction}/open', [DailyProductionController::class, 'open'])->name('daily-production.open');
    Route::get('daily-production/machine-stats', [DailyProductionController::class, 'getMachineStats'])->name('daily-production.machine-stats');
    Route::get('daily-production/operator-stats', [DailyProductionController::class, 'getOperatorStats'])->name('daily-production.operator-stats');
    
    // Protect edit and delete routes with permission middleware
    Route::get('daily-production/{dailyProduction}/edit', [DailyProductionController::class, 'edit'])->middleware('permission:edit,daily-production')->name('daily-production.edit');
    Route::put('daily-production/{dailyProduction}', [DailyProductionController::class, 'update'])->middleware('permission:edit,daily-production')->name('daily-production.update');
    Route::delete('daily-production/{dailyProduction}', [DailyProductionController::class, 'destroy'])->middleware('permission:delete,daily-production')->name('daily-production.destroy');

    // Gate Pass - Excel routes (must come before resource routes)
    Route::get('gate-pass/create-excel', [GatePassController::class, 'createExcel'])->name('gate-pass.create-excel');
    Route::post('gate-pass/store-multiple', [GatePassController::class, 'storeMultiple'])->name('gate-pass.store-multiple');
    
    // Gate Pass - Resource routes (excluding edit and delete which are handled separately)
    Route::resource('gate-pass', GatePassController::class)->except(['edit', 'update', 'destroy']);
    Route::get('gate-pass/{gatePass}/print', [GatePassController::class, 'print'])->name('gate-pass.print');
    Route::get('gate-pass/remaining-quantity/{stockIssued}', [GatePassController::class, 'getRemainingQuantity'])->name('gate-pass.remaining-quantity');
    
    // Protect edit and delete routes with permission middleware
    Route::get('gate-pass/{gatePass}/edit', [GatePassController::class, 'edit'])->middleware('permission:edit,gate-pass')->name('gate-pass.edit');
    Route::put('gate-pass/{gatePass}', [GatePassController::class, 'update'])->middleware('permission:edit,gate-pass')->name('gate-pass.update');
    Route::delete('gate-pass/{gatePass}', [GatePassController::class, 'destroy'])->middleware('permission:delete,gate-pass')->name('gate-pass.destroy');
    
    // Reports - Admin only
    Route::get('/reports', [StockManagementController::class, 'reports'])->middleware('admin')->name('reports');
});

// Master Data Routes (Admin Only) - Products, Mine Vendors, Condition Statuses
Route::middleware(['auth', 'verified', 'admin'])->prefix('stock-management')->name('stock-management.')->group(function () {
    // Products
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    
    // Protect edit and delete routes with permission middleware
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:edit,products')->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:edit,products')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:delete,products')->name('products.destroy');

    // Mine Vendors
    Route::resource('mine-vendors', MineVendorController::class);
    Route::patch('mine-vendors/{mineVendor}/toggle-status', [MineVendorController::class, 'toggleStatus'])->name('mine-vendors.toggle-status');
    
    // Protect edit and delete routes with permission middleware
    Route::get('mine-vendors/{mineVendor}/edit', [MineVendorController::class, 'edit'])->middleware('permission:edit,mine-vendors')->name('mine-vendors.edit');
    Route::put('mine-vendors/{mineVendor}', [MineVendorController::class, 'update'])->middleware('permission:edit,mine-vendors')->name('mine-vendors.update');
    Route::delete('mine-vendors/{mineVendor}', [MineVendorController::class, 'destroy'])->middleware('permission:delete,mine-vendors')->name('mine-vendors.destroy');

    // Condition Statuses
    Route::resource('condition-statuses', ConditionStatusController::class);
    Route::patch('condition-statuses/{conditionStatus}/toggle-status', [ConditionStatusController::class, 'toggleStatus'])->name('condition-statuses.toggle-status');
    
    // Protect edit and delete routes with permission middleware
    Route::get('condition-statuses/{conditionStatus}/edit', [ConditionStatusController::class, 'edit'])->middleware('permission:edit,condition-statuses')->name('condition-statuses.edit');
    Route::put('condition-statuses/{conditionStatus}', [ConditionStatusController::class, 'update'])->middleware('permission:edit,condition-statuses')->name('condition-statuses.update');
    Route::delete('condition-statuses/{conditionStatus}', [ConditionStatusController::class, 'destroy'])->middleware('permission:delete,condition-statuses')->name('condition-statuses.destroy');
});

// API Route for Condition Statuses - Accessible to all authenticated users (for dropdowns in forms)
Route::middleware(['auth', 'verified'])->prefix('stock-management')->name('stock-management.')->group(function () {
    Route::get('condition-statuses-api/active', [ConditionStatusController::class, 'getActive'])->name('condition-statuses.active');
});

// Master Data Routes (Admin Only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('master-data')->name('master-data.')->group(function () {
    // Machines
    Route::resource('machines', MachineController::class);

    // Operators
    Route::resource('operators', OperatorController::class);
});

// Accounting Routes (Admin Only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('accounting')->name('accounting.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AccountingController::class, 'dashboard'])->name('dashboard');

    // Financial Reports
    Route::get('/trial-balance', [AccountingController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('balance-sheet');
    Route::get('/income-statement', [AccountingController::class, 'incomeStatement'])->name('income-statement');
    Route::get('/general-ledger', [AccountingController::class, 'generalLedger'])->name('general-ledger');

    // Chart of Accounts
    Route::resource('chart-of-accounts', ChartOfAccountController::class);
    Route::patch('chart-of-accounts/{chartOfAccount}/toggle-status', [ChartOfAccountController::class, 'toggleStatus'])->name('chart-of-accounts.toggle-status');
    Route::get('chart-of-accounts-api/accounts', [ChartOfAccountController::class, 'getAccounts'])->name('chart-of-accounts.accounts');
    Route::post('chart-of-accounts/{chartOfAccount}/update-balance', [ChartOfAccountController::class, 'updateBalance'])->name('chart-of-accounts.update-balance');

    // Journal Entries
    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journalEntry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('journal-entries/{journalEntry}/reverse', [JournalEntryController::class, 'reverse'])->name('journal-entries.reverse');
    Route::post('journal-entries/{journalEntry}/approve', [JournalEntryController::class, 'approve'])->name('journal-entries.approve');
    Route::get('journal-entries/{journalEntry}/details', [JournalEntryController::class, 'getDetails'])->name('journal-entries.details');

    // Bank Payment Vouchers
    Route::resource('bank-payment-vouchers', BankPaymentVoucherController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('cash-payment-vouchers', CashPaymentVoucherController::class)->only(['index', 'create', 'store']);
    Route::resource('purchase-vouchers', PurchaseVoucherController::class)->only(['index', 'create', 'store']);

    // Auto-generate entries for ERM transactions
    Route::post('/generate-auto-entries', [AccountingController::class, 'generateAutoEntries'])->name('generate-auto-entries');
});

// Database Viewer Routes (Admin Only)
Route::middleware(['auth', 'verified', 'admin'])->prefix('database-viewer')->name('database-viewer.')->group(function () {
    Route::get('/', [DatabaseViewController::class, 'index'])->name('index');
    Route::get('/table/{tableName}', [DatabaseViewController::class, 'viewTable'])->name('table.view');
    Route::post('/execute-query', [DatabaseViewController::class, 'executeQuery'])->name('execute-query');
    Route::get('/table/{tableName}/schema', [DatabaseViewController::class, 'getTableSchema'])->name('table.schema');
    Route::get('/table/{tableName}/export', [DatabaseViewController::class, 'exportTable'])->name('table.export');

    // CRUD Operations
    Route::get('/table/{tableName}/create', [DatabaseViewController::class, 'createRecord'])->name('table.create');
    Route::post('/table/{tableName}/store', [DatabaseViewController::class, 'storeRecord'])->name('table.store');
    Route::get('/table/{tableName}/edit/{id}', [DatabaseViewController::class, 'editRecord'])->name('table.edit');
    Route::put('/table/{tableName}/update/{id}', [DatabaseViewController::class, 'updateRecord'])->name('table.update');
    Route::delete('/table/{tableName}/delete/{id}', [DatabaseViewController::class, 'deleteRecord'])->name('table.delete');
});

require __DIR__.'/auth.php';
