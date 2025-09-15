<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\StockAddition;
use Illuminate\Http\Request;

class DailyProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DailyProduction::with(['stockAddition.product', 'stockAddition.mineVendor']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('machine_name', 'like', "%{$search}%")
                  ->orWhere('product', 'like', "%{$search}%")
                  ->orWhere('operator_name', 'like', "%{$search}%")
                  ->orWhere('condition_status', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('stockAddition.product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('stockAddition.mineVendor', function ($vendorQuery) use ($search) {
                      $vendorQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('stockAddition', function ($stockQuery) use ($search) {
                      $stockQuery->where('stone', 'like', "%{$search}%")
                                ->orWhere('size_3d', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('stockAddition.product', function ($productQuery) use ($request) {
                $productQuery->where('id', $request->get('product_id'));
            });
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->whereHas('stockAddition.mineVendor', function ($vendorQuery) use ($request) {
                $vendorQuery->where('id', $request->get('vendor_id'));
            });
        }

        // Filter by machine
        if ($request->filled('machine_name')) {
            $query->where('machine_name', $request->get('machine_name'));
        }

        // Filter by operator
        if ($request->filled('operator_name')) {
            $query->where('operator_name', $request->get('operator_name'));
        }

        // Filter by condition status
        if ($request->filled('condition_status')) {
            $query->where('condition_status', $request->get('condition_status'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'product':
                $query->join('stock_additions', 'daily_production.stock_addition_id', '=', 'stock_additions.id')
                      ->join('products', 'stock_additions.product_id', '=', 'products.id')
                      ->orderBy('products.name', $sortOrder)
                      ->select('daily_production.*');
                break;
            case 'vendor':
                $query->join('stock_additions', 'daily_production.stock_addition_id', '=', 'stock_additions.id')
                      ->join('mine_vendors', 'stock_additions.mine_vendor_id', '=', 'mine_vendors.id')
                      ->orderBy('mine_vendors.name', $sortOrder)
                      ->select('daily_production.*');
                break;
            case 'machine':
                $query->orderBy('machine_name', $sortOrder);
                break;
            case 'operator':
                $query->orderBy('operator_name', $sortOrder);
                break;
            case 'pieces':
                $query->orderBy('total_pieces', $sortOrder);
                break;
            case 'sqft':
                $query->orderBy('total_sqft', $sortOrder);
                break;
            case 'condition':
                $query->orderBy('condition_status', $sortOrder);
                break;
            default:
                $query->orderBy('date', $sortOrder);
                break;
        }

        $dailyProduction = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = \App\Models\Product::orderBy('name')->get();
        $vendors = \App\Models\MineVendor::orderBy('name')->get();
        $machines = DailyProduction::distinct()->pluck('machine_name')->filter()->sort()->values();
        $operators = DailyProduction::distinct()->pluck('operator_name')->filter()->sort()->values();
        $conditionStatuses = DailyProduction::distinct()->pluck('condition_status')->filter()->sort()->values();

        return view('stock-management.daily-production.index', compact('dailyProduction', 'products', 'vendors', 'machines', 'operators', 'conditionStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $stockAdditionId = $request->get('stock_addition_id');
        $stockAddition = null;

        if ($stockAdditionId) {
            $stockAddition = StockAddition::with(['product', 'mineVendor'])
                ->findOrFail($stockAdditionId);
        }

        $availableStockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->where('available_pieces', '>', 0)
            ->orderBy('date', 'asc')
            ->get();

        return view('stock-management.daily-production.create', compact('stockAddition', 'availableStockAdditions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'machine_name' => 'required|string|max:255',
            'product' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'total_pieces' => 'required|integer|min:1',
            'total_sqft' => 'required|numeric|min:0',
            'condition_status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available
        if ($request->total_pieces > $stockAddition->available_pieces) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested production quantity exceeds available stock.');
        }

        $dailyProduction = DailyProduction::create($request->all());

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyProduction $dailyProduction)
    {
        $dailyProduction->load(['stockAddition.product', 'stockAddition.mineVendor']);

        return view('stock-management.daily-production.show', compact('dailyProduction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyProduction $dailyProduction)
    {
        $availableStockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->where('available_pieces', '>', 0)
            ->orWhere('id', $dailyProduction->stock_addition_id)
            ->orderBy('date', 'asc')
            ->get();

        return view('stock-management.daily-production.edit', compact('dailyProduction', 'availableStockAdditions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyProduction $dailyProduction)
    {
        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'machine_name' => 'required|string|max:255',
            'product' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'total_pieces' => 'required|integer|min:1',
            'total_sqft' => 'required|numeric|min:0',
            'condition_status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available (considering current production)
        $currentProduction = $dailyProduction->total_pieces;
        $availableAfterRestore = $stockAddition->available_pieces + $currentProduction;

        if ($request->total_pieces > $availableAfterRestore) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested production quantity exceeds available stock.');
        }

        $dailyProduction->update($request->all());

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyProduction $dailyProduction)
    {
        $dailyProduction->delete();

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production deleted successfully.');
    }

    /**
     * Get production statistics by machine.
     */
    public function getMachineStats(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $machineStats = DailyProduction::select('machine_name')
            ->selectRaw('SUM(total_pieces) as total_pieces')
            ->selectRaw('SUM(total_sqft) as total_sqft')
            ->selectRaw('COUNT(*) as production_days')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('machine_name')
            ->orderBy('total_pieces', 'desc')
            ->get();

        return response()->json($machineStats);
    }

    /**
     * Get production statistics by operator.
     */
    public function getOperatorStats(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $operatorStats = DailyProduction::select('operator_name')
            ->selectRaw('SUM(total_pieces) as total_pieces')
            ->selectRaw('SUM(total_sqft) as total_sqft')
            ->selectRaw('COUNT(*) as production_days')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('operator_name')
            ->orderBy('total_pieces', 'desc')
            ->get();

        return response()->json($operatorStats);
    }
}
