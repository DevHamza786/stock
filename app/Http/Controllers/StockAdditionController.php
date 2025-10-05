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
                  ->orWhere('length', 'like', "%{$search}%")
                  ->orWhere('height', 'like', "%{$search}%")
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
        } elseif ($sortBy === 'pid') {
            $query->orderBy('pid', $sortDirection);
        } elseif (in_array($sortBy, ['date', 'total_pieces', 'total_sqft', 'available_pieces'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('date', 'desc');
        }

        $stockAdditions = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $vendors = MineVendor::where('is_active', true)->orderBy('name')->get();
        $conditions = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();

        return view('stock-management.stock-additions.index', compact('stockAdditions', 'products', 'vendors', 'conditions'));
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
            'pid' => 'nullable|string|max:20|unique:stock_additions,pid',
            'product_id' => 'required|exists:products,id',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stone' => 'required|string|max:255',
            'length' => 'nullable|numeric|min:0.1',
            'height' => 'nullable|numeric|min:0.1',
            'diameter' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0.1',
            'total_pieces' => 'required|integer|min:1',
            'condition_status' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // Custom validation based on condition status
        $conditionStatus = strtolower($request->condition_status);
        if ($conditionStatus === 'block') {
            // For block condition, weight is required, length/height are not
            $request->validate([
                'weight' => 'required|numeric|min:0.1',
            ]);

        } else {
            // For other conditions, length and height are required
            $request->validate([
                'length' => 'required|numeric|min:0.1',
                'height' => 'required|numeric|min:0.1',
            ]);
        }

        // Calculate total_sqft and available_weight based on condition status
        $totalSqft = 0;
        $availableWeight = 0;
        
        if ($conditionStatus !== 'block') {
            // For non-block conditions, calculate total_sqft
            $length = $request->length ?? 0;
            $height = $request->height ?? 0;
            $totalPieces = $request->total_pieces ?? 0;
            
            if ($length > 0 && $height > 0 && $totalPieces > 0) {
                // Convert cm to sqft (1 cm² = 0.00107639 sqft)
                $cmToSqft = 0.00107639;
                $singlePieceSizeCm = $length * $height;
                $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                $totalSqft = $singlePieceSizeSqft * $totalPieces;
            }
        } else {
            // For block condition, calculate available_weight
            $weight = $request->weight ?? 0;
            $totalPieces = $request->total_pieces ?? 0;
            $availableWeight = $weight * $totalPieces;
        }

        $stockAddition = StockAddition::create(array_merge($request->all(), [
            'total_sqft' => $totalSqft,
            'available_weight' => $availableWeight
        ]));

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
        $conditionStatuses = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();

        return view('stock-management.stock-additions.edit', compact('stockAddition', 'products', 'mineVendors', 'conditionStatuses'));
    }

    /**
     * Update the specified resource in storage.
     * NO VALIDATION FOR TESTING
     */
    public function update(Request $request, StockAddition $stockAddition)
    {
        // Debug: Log everything about this request
        \Log::info('=== STOCK UPDATE DEBUG START ===');
        \Log::info('StockAddition Update - Request received (NO VALIDATION)', [
            'stock_addition_id' => $stockAddition->id,
            'method' => $request->method(),
            'url' => $request->url(),
            'all_request_data' => $request->all(),
            'weight_value' => $request->input('weight'),
            'total_pieces_value' => $request->input('total_pieces'),
            'condition_status' => $request->input('condition_status'),
            'has_been_issued' => $stockAddition->hasBeenIssued(),
            'current_weight' => $stockAddition->weight,
            'current_total_pieces' => $stockAddition->total_pieces,
        ]);

        try {
            // Validate PID if provided
            if ($request->filled('pid')) {
                $request->validate([
                    'pid' => 'string|max:20|unique:stock_additions,pid,' . $stockAddition->id,
                ]);
            }

            // Show what data will be updated
            $updateData = $request->all();
            unset($updateData['_token'], $updateData['_method']);
            
            // Handle NULL values based on condition status
            $conditionStatus = strtolower(trim($updateData['condition_status'] ?? ''));
            
            if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
                // For Block/Monuments condition: only keep weight and total_pieces, set others to NULL
                $blockData = [
                    'product_id' => $updateData['product_id'],
                    'mine_vendor_id' => $updateData['mine_vendor_id'],
                    'stone' => $updateData['stone'],
                    'condition_status' => $updateData['condition_status'],
                    'date' => $updateData['date'],
                    'weight' => !empty($updateData['weight']) ? $updateData['weight'] : null,
                    'total_pieces' => $updateData['total_pieces'],
                    
                    // Set dimension fields to NULL for Block/Monuments
                    'length' => null,
                    'height' => null,
                    'diameter' => null,
                    'total_sqft' => null,
                    'available_sqft' => null,
                    'size_3d' => null,
                    
                    // Calculate available_weight for Block/Monuments
                    'available_weight' => !empty($updateData['weight']) && !empty($updateData['total_pieces']) 
                        ? ($updateData['weight'] * $updateData['total_pieces']) 
                        : null
                ];
                
                $updateData = $blockData;
            } else {
                // For other conditions: keep dimension fields, set weight to NULL
                $sizeData = [
                    'product_id' => $updateData['product_id'],
                    'mine_vendor_id' => $updateData['mine_vendor_id'],
                    'stone' => $updateData['stone'],
                    'condition_status' => $updateData['condition_status'],
                    'date' => $updateData['date'],
                    'total_pieces' => $updateData['total_pieces'],
                    'length' => !empty($updateData['length']) ? $updateData['length'] : null,
                    'height' => !empty($updateData['height']) ? $updateData['height'] : null,
                    'diameter' => !empty($updateData['diameter']) ? $updateData['diameter'] : null,
                    
                    // Set weight to NULL for non-Block/Monuments conditions
                    'weight' => null,
                    'available_weight' => null
                ];
                
                // Calculate total_sqft for non-Block/Monuments conditions
                if (!empty($updateData['length']) && !empty($updateData['height'])) {
                    $cmToSqft = 0.00107639;
                    $totalPieces = $updateData['total_pieces'] ?? 0;
                    $singlePieceSizeCm = $updateData['length'] * $updateData['height'];
                    $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                    $sizeData['total_sqft'] = $singlePieceSizeSqft * $totalPieces;
                    $sizeData['available_sqft'] = $singlePieceSizeSqft * $totalPieces;
                }
                
                $updateData = $sizeData;
            }
            
            \Log::info('Update data prepared:', $updateData);
            
            $stockAddition->update($updateData);

            \Log::info('=== STOCK UPDATE DEBUG SUCCESS ===');

            return redirect()->route('stock-management.stock-additions.index')
                ->with('success', 'Stock addition updated successfully (NO VALIDATION - TEST MODE)');
        } catch (\Exception $e) {
            \Log::error('=== STOCK UPDATE DEBUG ERROR ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockAddition $stockAddition)
    {
        if ($stockAddition->stockIssued()->count() > 0) {
            return redirect()->route('stock-management.stock-additions.index')
                ->with('error', 'Cannot delete stock addition with existing stock issuances.');
        }

        $stockAddition->delete();

        return redirect()->route('stock-management.stock-additions.index')
            ->with('success', 'Stock addition deleted successfully.');
    }

    /**
     * Calculate square footage from dimensions.
     */
    public function calculateSqft(Request $request)
    {
        $length = $request->get('length');
        $height = $request->get('height');
        $pieces = $request->get('pieces', 1);

        if (empty($length) || empty($height)) {
            return response()->json(['sqft' => 0]);
        }

        $sqft = StockAddition::calculateSqftFromDimensions($length, $height);
        $totalSqft = $sqft * $pieces;

        return response()->json([
            'sqft_per_piece' => $sqft,
            'total_sqft' => $totalSqft
        ]);
    }
}
