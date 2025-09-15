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

require __DIR__.'/auth.php';
