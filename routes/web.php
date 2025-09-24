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

    // Stock Additions
    Route::resource('stock-additions', StockAdditionController::class);
    Route::post('stock-additions/calculate-sqft', [StockAdditionController::class, 'calculateSqft'])->name('stock-additions.calculate-sqft');

    // Stock Issued
    Route::resource('stock-issued', StockIssuedController::class);
    Route::get('stock-issued/available-quantity/{stockAddition}', [StockIssuedController::class, 'getAvailableQuantity'])->name('stock-issued.available-quantity');

    // Daily Production
    Route::resource('daily-production', DailyProductionController::class);
    Route::get('daily-production/machine-stats', [DailyProductionController::class, 'getMachineStats'])->name('daily-production.machine-stats');
    Route::get('daily-production/operator-stats', [DailyProductionController::class, 'getOperatorStats'])->name('daily-production.operator-stats');

    // Gate Pass
    Route::resource('gate-pass', GatePassController::class);
    Route::get('gate-pass/{gatePass}/print', [GatePassController::class, 'print'])->name('gate-pass.print');
    Route::get('gate-pass/remaining-quantity/{stockIssued}', [GatePassController::class, 'getRemainingQuantity'])->name('gate-pass.remaining-quantity');
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

require __DIR__.'/auth.php';
