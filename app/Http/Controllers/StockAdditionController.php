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

        $stockAdditions = $query->paginate(50)->withQueryString();

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
        if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
            // For block and monuments conditions, weight is required, length/height are not
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
        
        if ($conditionStatus !== 'block' && $conditionStatus !== 'monuments') {
            // For non-block/monuments conditions, calculate total_sqft
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
            // For block and monuments conditions, calculate available_weight
            $weight = $request->weight ?? 0;
            $totalPieces = $request->total_pieces ?? 0;
            $availableWeight = $weight * $totalPieces;
        }

        $stockAddition = StockAddition::create(array_merge($request->all(), [
            'total_sqft' => $totalSqft,
            'available_sqft' => $totalSqft,
            'available_weight' => $availableWeight,
            'available_pieces' => $request->total_pieces
        ]));

        return redirect()->route('stock-management.stock-additions.index')
            ->with('success', 'Stock addition created successfully.');
    }

    /**
     * Store multiple stock additions from Excel-style form.
     */
    public function storeMultiple(Request $request)
    {
        // Validate common fields
        $request->validate([
            'date' => 'required|date',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stocks' => 'required|array|min:1',
            'stocks.*.product_id' => 'required|exists:products,id',
            'stocks.*.condition_status' => 'required|string|max:255',
            'stocks.*.stone' => 'required|string|max:255',
            'stocks.*.total_pieces' => 'required|integer|min:1',
        ]);

        $createdCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            foreach ($request->stocks as $index => $stockData) {
                // Skip empty rows
                if (empty($stockData['product_id']) || empty($stockData['condition_status']) || 
                    empty($stockData['stone']) || empty($stockData['total_pieces'])) {
                    continue;
                }

                $conditionStatus = strtolower(trim($stockData['condition_status']));
                
                // Validate condition-specific fields
                if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
                    if (empty($stockData['weight']) || !is_numeric($stockData['weight']) || $stockData['weight'] <= 0) {
                        $errors[] = "Row " . ($index + 1) . ": Weight is required for Block/Monuments condition.";
                        continue;
                    }
                } else {
                    if (empty($stockData['length']) || !is_numeric($stockData['length']) || $stockData['length'] <= 0 ||
                        empty($stockData['height']) || !is_numeric($stockData['height']) || $stockData['height'] <= 0) {
                        $errors[] = "Row " . ($index + 1) . ": Length and Height are required for this condition.";
                        continue;
                    }
                }

                // Calculate totals
                $totalSqft = 0;
                $availableWeight = 0;
                
                if ($conditionStatus !== 'block' && $conditionStatus !== 'monuments') {
                    // Calculate total_sqft for non-block conditions
                    $length = floatval($stockData['length'] ?? 0);
                    $height = floatval($stockData['height'] ?? 0);
                    $totalPieces = intval($stockData['total_pieces']);
                    
                    if ($length > 0 && $height > 0 && $totalPieces > 0) {
                        $cmToSqft = 0.00107639;
                        $singlePieceSizeCm = $length * $height;
                        $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                        $totalSqft = $singlePieceSizeSqft * $totalPieces;
                    }
                } else {
                    // Calculate available_weight for block conditions
                    $weight = floatval($stockData['weight'] ?? 0);
                    $totalPieces = intval($stockData['total_pieces']);
                    $availableWeight = $weight * $totalPieces;
                }

                // Create stock addition
                StockAddition::create([
                    'product_id' => $stockData['product_id'],
                    'mine_vendor_id' => $request->mine_vendor_id,
                    'stone' => $stockData['stone'],
                    'condition_status' => $stockData['condition_status'],
                    'length' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['length'] ?? null),
                    'height' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['height'] ?? null),
                    'diameter' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['diameter'] ?? null),
                    'weight' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? floatval($stockData['weight']) : null,
                    'total_pieces' => intval($stockData['total_pieces']),
                    'total_sqft' => $totalSqft,
                    'available_sqft' => $totalSqft,
                    'available_weight' => $availableWeight,
                    'available_pieces' => intval($stockData['total_pieces']),
                    'date' => $request->date,
                    'pid' => $stockData['pid'] ?? null,
                ]);

                $createdCount++;
            }

            if (!empty($errors)) {
                \DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Some rows had validation errors: ' . implode(' ', $errors));
            }

            \DB::commit();

            return redirect()->route('stock-management.stock-additions.index')
                ->with('success', "Successfully created {$createdCount} stock addition(s).");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Multiple stock addition error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create stock additions. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockAddition $stockAddition)
    {
        $stockAddition->load(['product', 'mineVendor', 'stockIssued', 'dailyProduction', 'stockLogs']);

        // Check if required relationships exist
        if (!$stockAddition->product) {
            return redirect()->route('stock-management.stock-additions.index')
                ->with('error', 'This stock addition has an invalid product reference. Please contact administrator.');
        }
        
        if (!$stockAddition->mineVendor) {
            return redirect()->route('stock-management.stock-additions.index')
                ->with('error', 'This stock addition has an invalid vendor reference. Please contact administrator.');
        }

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
     * Show the Excel-style edit form for multiple stock additions.
     */
    public function editExcel(Request $request)
    {
        $productIds = $request->get('product_ids', []);
        $vendorIds = $request->get('vendor_ids', []);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $limitParam = $request->get('limit', 1000); // Default to 1000 records, but allow override
        $limit = ($limitParam === 'all') ? null : $limitParam;
        
        $query = StockAddition::with(['product', 'mineVendor']);
        
        if (!empty($productIds)) {
            $query->whereIn('product_id', $productIds);
        }
        
        if (!empty($vendorIds)) {
            $query->whereIn('mine_vendor_id', $vendorIds);
        }
        
        if ($dateFrom) {
            $query->where('date', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->where('date', '<=', $dateTo);
        }
        
        // Apply limit to prevent memory issues with very large datasets
        if ($limit) {
            $stockAdditions = $query->orderBy('date', 'desc')->limit($limit)->get();
        } else {
            $stockAdditions = $query->orderBy('date', 'desc')->get();
        }
        
        // Get total count for information
        $totalCount = StockAddition::count();
        $filteredCount = $query->count();
        
        $products = Product::where('is_active', true)->get();
        $mineVendors = MineVendor::where('is_active', true)->get();
        $conditionStatuses = \App\Models\ConditionStatus::where('is_active', true)->ordered()->get();

        return view('stock-management.stock-additions.edit-excel', compact('stockAdditions', 'products', 'mineVendors', 'conditionStatuses', 'totalCount', 'filteredCount', 'limit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockAddition $stockAddition)
    {
        // Basic validation
        $request->validate([
            'pid' => 'nullable|string|max:20|unique:stock_additions,pid,' . $stockAddition->id,
            'product_id' => 'required|exists:products,id',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stone' => 'required|string|max:255',
            'length' => 'nullable|numeric|min:0.1',
            'height' => 'nullable|numeric|min:0.1',
            'diameter' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0.1',
            'total_pieces' => 'required|integer|min:1',
            'available_pieces' => 'nullable|integer|min:0',
            'available_sqft' => 'nullable|numeric|min:0',
            'available_weight' => 'nullable|numeric|min:0',
            'condition_status' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // Custom validation based on condition status
        $conditionStatus = strtolower(trim($request->condition_status));
        if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
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

        // Validate available quantities don't exceed total quantities
        if ($request->filled('available_pieces') && $request->available_pieces > $request->total_pieces) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Available pieces cannot be greater than total pieces.');
        }

        // For Block/Monuments, validate available weight
        if (($conditionStatus === 'block' || $conditionStatus === 'monuments') && $request->filled('available_weight')) {
            $totalWeight = ($request->weight ?? 0) * ($request->total_pieces ?? 0);
            if ($request->available_weight > $totalWeight) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Available weight cannot be greater than total weight (' . number_format($totalWeight, 2) . ' kg).');
            }
        }

        // For other conditions, validate available sqft
        if (!in_array($conditionStatus, ['block', 'monuments']) && $request->filled('available_sqft')) {
            if ($request->filled('length') && $request->filled('height')) {
                $cmToSqft = 0.00107639;
                $singlePieceSizeCm = $request->length * $request->height;
                $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                $totalSqft = $singlePieceSizeSqft * ($request->total_pieces ?? 0);
                
                if ($request->available_sqft > $totalSqft) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Available sqft cannot be greater than total sqft (' . number_format($totalSqft, 2) . ' sqft).');
                }
            }
        }

        try {

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
                    
                    // Use submitted available_pieces if provided, otherwise use total_pieces
                    'available_pieces' => isset($updateData['available_pieces']) 
                        ? $updateData['available_pieces'] 
                        : $updateData['total_pieces'],
                    
                    // Set dimension fields to NULL for Block/Monuments
                    'length' => null,
                    'height' => null,
                    'diameter' => null,
                    'total_sqft' => null,
                    'available_sqft' => null,
                    'size_3d' => null,
                    
                    // Use submitted available_weight if provided, otherwise calculate
                    'available_weight' => isset($updateData['available_weight']) 
                        ? $updateData['available_weight']
                        : (!empty($updateData['weight']) && !empty($updateData['total_pieces']) 
                            ? ($updateData['weight'] * $updateData['total_pieces']) 
                            : 0)
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
                    
                    // Use submitted available_pieces if provided, otherwise use total_pieces
                    'available_pieces' => isset($updateData['available_pieces']) 
                        ? $updateData['available_pieces'] 
                        : $updateData['total_pieces'],
                    
                    'length' => !empty($updateData['length']) ? $updateData['length'] : null,
                    'height' => !empty($updateData['height']) ? $updateData['height'] : null,
                    'diameter' => !empty($updateData['diameter']) ? $updateData['diameter'] : null,
                    
                    // Set weight to NULL for non-Block/Monuments conditions
                    'weight' => null,
                    'available_weight' => 0
                ];
                
                // Calculate total_sqft for non-Block/Monuments conditions
                if (!empty($updateData['length']) && !empty($updateData['height'])) {
                    $cmToSqft = 0.00107639;
                    $totalPieces = $updateData['total_pieces'] ?? 0;
                    $singlePieceSizeCm = $updateData['length'] * $updateData['height'];
                    $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                    $sizeData['total_sqft'] = $singlePieceSizeSqft * $totalPieces;
                    
                    // Use submitted available_sqft if provided, otherwise calculate
                    $sizeData['available_sqft'] = isset($updateData['available_sqft']) 
                        ? $updateData['available_sqft']
                        : ($singlePieceSizeSqft * $totalPieces);
                } else {
                    // Set to NULL if no dimensions provided
                    $sizeData['total_sqft'] = null;
                    $sizeData['available_sqft'] = isset($updateData['available_sqft']) 
                        ? $updateData['available_sqft']
                        : null;
                }
                
                $updateData = $sizeData;
            }
            
            \Log::info('Update data prepared:', $updateData);
            
            // Log before update
            \Log::info('Before update - Stock ID: ' . $stockAddition->id);
            \Log::info('Before update - Current values:', [
                'weight' => $stockAddition->weight,
                'total_pieces' => $stockAddition->total_pieces,
                'available_pieces' => $stockAddition->available_pieces,
                'available_weight' => $stockAddition->available_weight
            ]);
            
            $stockAddition->update($updateData);
            
            // Refresh and log after update
            $stockAddition->refresh();
            \Log::info('After update - Updated values:', [
                'weight' => $stockAddition->weight,
                'total_pieces' => $stockAddition->total_pieces,
                'available_pieces' => $stockAddition->available_pieces,
                'available_weight' => $stockAddition->available_weight
            ]);

            \Log::info('=== STOCK UPDATE DEBUG SUCCESS ===');

            return redirect()->route('stock-management.stock-additions.index')
                ->with('success', 'Stock addition updated successfully.');
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
     * Update multiple stock additions from Excel-style form.
     */
    public function updateMultiple(Request $request)
    {
        // Validate common fields
        $request->validate([
            'date' => 'required|date',
            'mine_vendor_id' => 'required|exists:mine_vendors,id',
            'stocks' => 'required|array|min:1',
            'stocks.*.id' => 'nullable|exists:stock_additions,id',
            'stocks.*.product_id' => 'required|exists:products,id',
            'stocks.*.condition_status' => 'required|string|max:255',
            'stocks.*.stone' => 'required|string|max:255',
            'stocks.*.total_pieces' => 'required|integer|min:1',
            'stocks.*.available_pieces' => 'required|integer|min:0',
        ]);

        $updatedCount = 0;
        $createdCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            foreach ($request->stocks as $index => $stockData) {
                // Skip empty rows
                if (empty($stockData['product_id']) || empty($stockData['condition_status']) || 
                    empty($stockData['stone']) || empty($stockData['total_pieces'])) {
                    continue;
                }

                $conditionStatus = strtolower(trim($stockData['condition_status']));
                
                // Validate condition-specific fields
                if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
                    if (empty($stockData['weight']) || !is_numeric($stockData['weight']) || $stockData['weight'] <= 0) {
                        $errors[] = "Row " . ($index + 1) . ": Weight is required for Block/Monuments condition.";
                        continue;
                    }
                } else {
                    if (empty($stockData['length']) || !is_numeric($stockData['length']) || $stockData['length'] <= 0 ||
                        empty($stockData['height']) || !is_numeric($stockData['height']) || $stockData['height'] <= 0) {
                        $errors[] = "Row " . ($index + 1) . ": Length and Height are required for this condition.";
                        continue;
                    }
                }

                // Validate available pieces doesn't exceed total pieces
                if (intval($stockData['available_pieces']) > intval($stockData['total_pieces'])) {
                    $errors[] = "Row " . ($index + 1) . ": Available pieces cannot exceed total pieces.";
                    continue;
                }

                // Calculate totals
                $totalSqft = 0;
                $availableWeight = 0;
                
                if ($conditionStatus !== 'block' && $conditionStatus !== 'monuments') {
                    // Calculate total_sqft for non-block conditions
                    $length = floatval($stockData['length'] ?? 0);
                    $height = floatval($stockData['height'] ?? 0);
                    $totalPieces = intval($stockData['total_pieces']);
                    
                    if ($length > 0 && $height > 0 && $totalPieces > 0) {
                        $cmToSqft = 0.00107639;
                        $singlePieceSizeCm = $length * $height;
                        $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                        $totalSqft = $singlePieceSizeSqft * $totalPieces;
                    }
                } else {
                    // Calculate available_weight for block conditions
                    $weight = floatval($stockData['weight'] ?? 0);
                    $totalPieces = intval($stockData['total_pieces']);
                    $availableWeight = $weight * $totalPieces;
                }

                $stockDataArray = [
                    'product_id' => $stockData['product_id'],
                    'mine_vendor_id' => $request->mine_vendor_id,
                    'stone' => $stockData['stone'],
                    'condition_status' => $stockData['condition_status'],
                    'length' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['length'] ?? null),
                    'height' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['height'] ?? null),
                    'diameter' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? null : ($stockData['diameter'] ?? null),
                    'weight' => $conditionStatus === 'block' || $conditionStatus === 'monuments' ? floatval($stockData['weight']) : null,
                    'total_pieces' => intval($stockData['total_pieces']),
                    'available_pieces' => intval($stockData['available_pieces']),
                    'total_sqft' => $totalSqft,
                    'available_sqft' => $totalSqft,
                    'available_weight' => $availableWeight,
                    'date' => $request->date,
                    'pid' => $stockData['pid'] ?? null,
                ];

                if (!empty($stockData['id'])) {
                    // Update existing stock addition
                    $stockAddition = StockAddition::findOrFail($stockData['id']);
                    $stockAddition->update($stockDataArray);
                    $updatedCount++;
                } else {
                    // Create new stock addition
                    StockAddition::create($stockDataArray);
                    $createdCount++;
                }
            }

            if (!empty($errors)) {
                \DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Some rows had validation errors: ' . implode(' ', $errors));
            }

            \DB::commit();

            $message = [];
            if ($updatedCount > 0) $message[] = "Updated {$updatedCount} stock addition(s)";
            if ($createdCount > 0) $message[] = "Created {$createdCount} stock addition(s)";
            
            return redirect()->route('stock-management.stock-additions.index')
                ->with('success', implode('. ', $message) . '.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Multiple stock update error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update stock additions. Please try again.');
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
