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
use App\Http\Controllers\MachineController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\DatabaseViewController;
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

// Stock Management Routes
Route::middleware(['auth', 'verified'])->prefix('stock-management')->name('stock-management.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StockManagementController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [StockManagementController::class, 'reports'])->name('reports');
    Route::get('/stock-levels', [StockManagementController::class, 'getStockLevels'])->name('stock-levels');
    Route::get('/available-stock', [StockManagementController::class, 'getAvailableStock'])->name('available-stock');

    // Products
    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Mine Vendors
    Route::resource('mine-vendors', MineVendorController::class);
    Route::patch('mine-vendors/{mineVendor}/toggle-status', [MineVendorController::class, 'toggleStatus'])->name('mine-vendors.toggle-status');

    // Condition Statuses
    Route::resource('condition-statuses', ConditionStatusController::class);
    Route::patch('condition-statuses/{conditionStatus}/toggle-status', [ConditionStatusController::class, 'toggleStatus'])->name('condition-statuses.toggle-status');
    Route::get('condition-statuses-api/active', [ConditionStatusController::class, 'getActive'])->name('condition-statuses.active');

    // Stock Additions - Excel routes (must come before resource routes)
    Route::get('stock-additions/edit-excel', [StockAdditionController::class, 'editExcel'])->name('stock-additions.edit-excel');
    Route::put('stock-additions/update-multiple', [StockAdditionController::class, 'updateMultiple'])->name('stock-additions.update-multiple');
    Route::post('stock-additions/store-multiple', [StockAdditionController::class, 'storeMultiple'])->name('stock-additions.store-multiple');
    Route::post('stock-additions/calculate-sqft', [StockAdditionController::class, 'calculateSqft'])->name('stock-additions.calculate-sqft');
    
    // Stock Additions - Resource routes
    Route::resource('stock-additions', StockAdditionController::class);

    // TEMPORARY TEST ROUTE
    Route::get('stock-additions/{stockAddition}/edit-test', function ($stockAdditionId) {
        $stockAddition = \App\Models\StockAddition::findOrFail($stockAdditionId);
        $products = \App\Models\Product::where('is_active', true)->get();
        $mineVendors = \App\Models\MineVendor::where('is_active', true)->get();
        $conditionStatuses = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();
        return view('stock-management.stock-additions.edit-test', compact('stockAddition', 'products', 'mineVendors', 'conditionStatuses'));
    })->name('stock-additions.edit-test');

    // Stock Issued
    Route::resource('stock-issued', StockIssuedController::class);
    Route::get('stock-issued/available-quantity/{stockAddition}', [StockIssuedController::class, 'getAvailableQuantity'])->name('stock-issued.available-quantity');

    // Daily Production
    // Daily Production - Excel routes (must come before resource routes)
    Route::get('daily-production/create-excel', [DailyProductionController::class, 'createExcel'])->name('daily-production.create-excel');
    Route::post('daily-production/store-multiple', [DailyProductionController::class, 'storeMultiple'])->name('daily-production.store-multiple');
    
    // Daily Production - Resource routes
    Route::resource('daily-production', DailyProductionController::class);
    Route::get('daily-production/{dailyProduction}/print', [DailyProductionController::class, 'print'])->name('daily-production.print');
    Route::patch('daily-production/{dailyProduction}/close', [DailyProductionController::class, 'close'])->name('daily-production.close');
    Route::patch('daily-production/{dailyProduction}/open', [DailyProductionController::class, 'open'])->name('daily-production.open');
    Route::get('daily-production/machine-stats', [DailyProductionController::class, 'getMachineStats'])->name('daily-production.machine-stats');
    Route::get('daily-production/operator-stats', [DailyProductionController::class, 'getOperatorStats'])->name('daily-production.operator-stats');

    // Gate Pass - Excel routes (must come before resource routes)
    Route::get('gate-pass/create-excel', [GatePassController::class, 'createExcel'])->name('gate-pass.create-excel');
    Route::post('gate-pass/store-multiple', [GatePassController::class, 'storeMultiple'])->name('gate-pass.store-multiple');
    
    // Gate Pass - Resource routes
    Route::resource('gate-pass', GatePassController::class);
    Route::get('gate-pass/{gatePass}/print', [GatePassController::class, 'print'])->name('gate-pass.print');
    Route::get('gate-pass/remaining-quantity/{stockIssued}', [GatePassController::class, 'getRemainingQuantity'])->name('gate-pass.remaining-quantity');
});

// Master Data Routes
Route::middleware(['auth', 'verified'])->prefix('master-data')->name('master-data.')->group(function () {
    // Machines
    Route::resource('machines', MachineController::class);

    // Operators
    Route::resource('operators', OperatorController::class);
});

// Accounting Routes
Route::middleware(['auth', 'verified'])->prefix('accounting')->name('accounting.')->group(function () {
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

    // Auto-generate entries for ERM transactions
    Route::post('/generate-auto-entries', [AccountingController::class, 'generateAutoEntries'])->name('generate-auto-entries');
});

// Database Viewer Routes (Protected with auth middleware)
Route::middleware(['auth', 'verified'])->prefix('database-viewer')->name('database-viewer.')->group(function () {
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
