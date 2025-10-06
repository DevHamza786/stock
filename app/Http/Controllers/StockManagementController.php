<?php

namespace App\Http\Controllers;

use App\Models\StockAddition;
use App\Models\StockIssued;
use App\Models\DailyProduction;
use App\Models\GatePass;
use App\Models\Product;
use App\Models\MineVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockManagementController extends Controller
{
    /**
     * Display the stock management dashboard.
     */
    public function dashboard()
    {
        // Get summary statistics
        $totalStockAdditions = StockAddition::count();
        $totalStockIssued = StockIssued::where('purpose', '!=', 'Gate Pass Dispatch')->sum('quantity_issued');
        $totalDailyProduction = DailyProduction::with('items')->get()->sum(function($production) {
            return $production->items->sum('total_pieces');
        });
        $totalGatePasses = GatePass::count();

        // Get available stock summary
        $availableStock = StockAddition::where('available_pieces', '>', 0)->sum('available_pieces');
        $availableSqft = StockAddition::where('available_sqft', '>', 0)->sum('available_sqft');

        // Get recent activities
        $recentStockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentStockIssued = StockIssued::with(['stockAddition.product'])
            ->where('purpose', '!=', 'Gate Pass Dispatch') // Exclude gate pass dispatched stock
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentDailyProduction = DailyProduction::with(['stockAddition.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentGatePasses = GatePass::with(['stockIssued.stockAddition.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get stock levels by product
        $stockLevelsByProduct = Product::with(['stockAdditions' => function($query) {
            $query->where('available_pieces', '>', 0);
        }])
        ->get()
        ->map(function($product) {
            return [
                'product' => $product->name,
                'available_pieces' => $product->stockAdditions->sum('available_pieces'),
                'available_sqft' => $product->stockAdditions->sum('available_sqft')
            ];
        });

        // Get monthly production data
        $monthlyProduction = DailyProduction::join('daily_production_items', 'daily_production.id', '=', 'daily_production_items.daily_production_id')
            ->select(
                DB::raw('strftime("%Y-%m", daily_production.date) as month'),
                DB::raw('SUM(daily_production_items.total_pieces) as total_pieces'),
                DB::raw('SUM(daily_production_items.total_sqft) as total_sqft')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('stock-management.dashboard', compact(
            'totalStockAdditions',
            'totalStockIssued',
            'totalDailyProduction',
            'totalGatePasses',
            'availableStock',
            'availableSqft',
            'recentStockAdditions',
            'recentStockIssued',
            'recentDailyProduction',
            'recentGatePasses',
            'stockLevelsByProduct',
            'monthlyProduction'
        ));
    }

    /**
     * Display stock reports.
     */
    public function reports(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $productId = $request->get('product_id');

        // Get all products for filter dropdown
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Summary statistics
        $totalStockAdded = StockAddition::when($productId, function($query) use ($productId) {
            return $query->where('product_id', $productId);
        })->whereBetween('date', [$startDate, $endDate])->sum('total_pieces');

        $totalStockIssued = StockIssued::when($productId, function($query) use ($productId) {
            return $query->whereHas('stockAddition', function($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        })->whereBetween('date', [$startDate, $endDate])->sum('quantity_issued');

        $totalProduction = DailyProduction::join('daily_production_items', 'daily_production.id', '=', 'daily_production_items.daily_production_id')
            ->when($productId, function($query) use ($productId) {
                return $query->whereHas('stockAddition', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            })
            ->whereBetween('daily_production.date', [$startDate, $endDate])
            ->sum('daily_production_items.total_pieces');

        $totalGatePasses = GatePass::when($productId, function($query) use ($productId) {
            return $query->whereHas('stockIssued.stockAddition', function($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        })->whereBetween('date', [$startDate, $endDate])->count();

        // Monthly data for charts
        $monthlyStockAdditions = StockAddition::select(
            DB::raw('strftime("%Y-%m", date) as month'),
            DB::raw('SUM(total_pieces) as total_pieces')
        )
        ->when($productId, function($query) use ($productId) {
            return $query->where('product_id', $productId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        $monthlyProduction = DailyProduction::join('daily_production_items', 'daily_production.id', '=', 'daily_production_items.daily_production_id')
            ->select(
                DB::raw('strftime("%Y-%m", daily_production.date) as month'),
                DB::raw('SUM(daily_production_items.total_pieces) as total_pieces')
            )
            ->when($productId, function($query) use ($productId) {
                return $query->whereHas('stockAddition', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                });
            })
        ->whereBetween('daily_production.date', [$startDate, $endDate])
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        // Top products by stock
        $topProducts = Product::withCount(['stockAdditions as total_stock' => function($query) {
            $query->select(DB::raw('SUM(total_pieces)'));
        }])
        ->orderBy('total_stock', 'desc')
        ->limit(5)
        ->get();

        // Top vendors by stock
        $topVendors = MineVendor::withCount(['stockAdditions as total_stock' => function($query) {
            $query->select(DB::raw('SUM(total_pieces)'));
        }])
        ->orderBy('total_stock', 'desc')
        ->limit(5)
        ->get();

        return view('stock-management.reports', compact(
            'totalStockAdded',
            'totalStockIssued',
            'totalProduction',
            'totalGatePasses',
            'monthlyStockAdditions',
            'monthlyProduction',
            'topProducts',
            'topVendors',
            'products',
            'startDate',
            'endDate',
            'productId'
        ));
    }

    /**
     * Get stock levels for a specific product.
     */
    public function getStockLevels(Request $request)
    {
        $productId = $request->get('product_id');

        $stockLevels = StockAddition::with(['product', 'mineVendor'])
            ->when($productId, function($query) use ($productId) {
                return $query->where('product_id', $productId);
            })
            ->where('available_pieces', '>', 0)
            ->whereDoesntHave('stockIssued', function ($query) {
                $query->where('purpose', 'Gate Pass Dispatch');
            })
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($stockLevels);
    }

    /**
     * Get available stock for issuing.
     */
    public function getAvailableStock(Request $request)
    {
        $productId = $request->get('product_id');

        $availableStock = StockAddition::with(['product', 'mineVendor'])
            ->where('product_id', $productId)
            ->where('available_pieces', '>', 0)
            ->whereDoesntHave('stockIssued', function ($query) {
                $query->where('purpose', 'Gate Pass Dispatch');
            })
            ->orderBy('date', 'asc') // FIFO - First In First Out
            ->get();

        return response()->json($availableStock);
    }
}
