<?php

namespace App\Http\Controllers;

use App\Models\StockAddition;
use App\Models\Product;
use App\Models\MineVendor;
use Illuminate\Http\Request;

class StockAdditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockAddition::with(['product', 'mineVendor']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('stone', 'like', "%{$search}%")
                  ->orWhere('size_3d', 'like', "%{$search}%")
                  ->orWhere('condition_status', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('mineVendor', function ($vendorQuery) use ($search) {
                      $vendorQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('mine_vendor_id', $request->get('vendor_id'));
        }

        // Filter by condition status
        if ($request->filled('condition_status')) {
            $query->where('condition_status', $request->get('condition_status'));
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            if ($request->get('stock_status') === 'available') {
                $query->where('available_pieces', '>', 0);
            } elseif ($request->get('stock_status') === 'out_of_stock') {
                $query->where('available_pieces', '=', 0);
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortDirection = $request->get('sort_direction', 'desc');

        if ($sortBy === 'product') {
            $query->join('products', 'stock_additions.product_id', '=', 'products.id')
                  ->orderBy('products.name', $sortDirection)
                  ->select('stock_additions.*');
        } elseif ($sortBy === 'vendor') {
            $query->join('mine_vendors', 'stock_additions.mine_vendor_id', '=', 'mine_vendors.id')
                  ->orderBy('mine_vendors.name', $sortDirection)
                  ->select('stock_additions.*');
        } elseif (in_array($sortBy, ['date', 'total_pieces', 'total_sqft', 'available_pieces'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('date', 'desc');
        }

        $stockAdditions = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $vendors = MineVendor::where('is_active', true)->orderBy('name')->get();
        $conditionStatuses = StockAddition::distinct()->pluck('condition_status')->filter();

        return view('stock-management.stock-additions.index', compact('stockAdditions', 'products', 'vendors', 'conditionStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $mineVendors = MineVendor::where('is_active', true)->get();
        $conditionStatuses = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();

        return view('stock-management.stock-additions.create', compact('products', 'mineVendors', 'conditionStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stone' => 'required|string|max:255',
            'size_3d' => 'required|string|max:10',
            'total_pieces' => 'required|integer|min:1',
            'condition_status' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::create($request->all());

        return redirect()->route('stock-management.stock-additions.index')
            ->with('success', 'Stock addition created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockAddition $stockAddition)
    {
        $stockAddition->load(['product', 'mineVendor', 'stockIssued', 'dailyProduction']);

        return view('stock-management.stock-additions.show', compact('stockAddition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockAddition $stockAddition)
    {
        $products = Product::where('is_active', true)->get();
        $mineVendors = MineVendor::where('is_active', true)->get();

        return view('stock-management.stock-additions.edit', compact('stockAddition', 'products', 'mineVendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockAddition $stockAddition)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stone' => 'required|string|max:255',
            'size_3d' => 'required|string|max:10',
            'total_pieces' => 'required|integer|min:1',
            'condition_status' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        $stockAddition->update($request->all());

        return redirect()->route('stock-management.stock-additions.index')
            ->with('success', 'Stock addition updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockAddition $stockAddition)
    {
        // Check if there are any stock issuances
        if ($stockAddition->stockIssued()->count() > 0) {
            return redirect()->route('stock-management.stock-additions.index')
                ->with('error', 'Cannot delete stock addition with existing stock issuances.');
        }

        $stockAddition->delete();

        return redirect()->route('stock-management.stock-additions.index')
            ->with('success', 'Stock addition deleted successfully.');
    }

    /**
     * Calculate square footage from size_3d.
     */
    public function calculateSqft(Request $request)
    {
        $size3d = $request->get('size_3d');
        $pieces = $request->get('pieces', 1);

        if (empty($size3d)) {
            return response()->json(['sqft' => 0]);
        }

        $sqft = StockAddition::calculateSqftFromSize3d($size3d);
        $totalSqft = $sqft * $pieces;

        return response()->json([
            'sqft_per_piece' => $sqft,
            'total_sqft' => $totalSqft
        ]);
    }
}
