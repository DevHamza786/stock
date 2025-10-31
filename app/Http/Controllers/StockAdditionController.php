<?php

namespace App\Http\Controllers;

use App\Models\StockAddition;
use App\Models\Product;
use App\Models\MineVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Get per_page parameter with default of 200, max of 1000
        $perPage = $request->get('per_page', 200);
        $perPage = min($perPage, 1000); // Limit to max 1000 records per page
        
        $stockAdditions = $query->paginate($perPage)->withQueryString();

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

        // Debug: Log validation success WITH condition_status
        \Log::info('=== STEP 1: Validation passed ===', [
            'mine_vendor_id' => $request->mine_vendor_id,
            'product_id' => $request->product_id,
            'stone' => $request->stone,
            'condition_status' => $request->condition_status ?? 'NOT_PROVIDED',
            'condition_status_exists' => $request->has('condition_status'),
            'all_request_keys' => array_keys($request->all())
        ]);

        // Custom validation based on condition status
        $conditionStatus = strtolower(trim($request->condition_status));
        \Log::info('=== STEP 2: Condition status processed ===', [
            'condition_status' => $conditionStatus,
            'is_block_or_monuments' => ($conditionStatus === 'block' || $conditionStatus === 'monuments')
        ]);
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

        \Log::info('=== STEP 3: Starting validation checks ===');
        
        // Validate available quantities don't exceed total quantities
        if ($request->filled('available_pieces') && $request->available_pieces > $request->total_pieces) {
            \Log::warning('=== EARLY RETURN: Available pieces validation failed ===');
            return redirect()->back()
                ->withInput()
                ->with('error', 'Available pieces cannot be greater than total pieces.');
        }
        \Log::info('=== STEP 3.1: Available pieces validation passed ===');

        // For Block/Monuments, validate available weight
        if (($conditionStatus === 'block' || $conditionStatus === 'monuments') && $request->filled('available_weight')) {
            $totalWeight = ($request->weight ?? 0) * ($request->total_pieces ?? 0);
            if ($request->available_weight > $totalWeight) {
                \Log::warning('=== EARLY RETURN: Available weight validation failed ===');
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Available weight cannot be greater than total weight (' . number_format($totalWeight, 2) . ' kg).');
            }
        }
        \Log::info('=== STEP 3.2: Available weight validation passed ===');

        // For other conditions, validate available sqft
        // But be lenient if condition_status is changing - allow update and recalculate later
        $oldConditionStatus = strtolower(trim($stockAddition->condition_status ?? ''));
        $isConditionStatusChanging = $conditionStatus !== $oldConditionStatus;
        
        if (!in_array($conditionStatus, ['block', 'monuments']) && $request->filled('available_sqft')) {
            if ($request->filled('length') && $request->filled('height')) {
                $cmToSqft = 0.00107639;
                $singlePieceSizeCm = $request->length * $request->height;
                $singlePieceSizeSqft = $singlePieceSizeCm * $cmToSqft;
                $totalSqft = $singlePieceSizeSqft * ($request->total_pieces ?? 0);
                
                \Log::info('=== STEP 3.3: Checking available sqft ===', [
                    'available_sqft' => $request->available_sqft,
                    'calculated_total_sqft' => $totalSqft,
                    'is_condition_status_changing' => $isConditionStatusChanging,
                    'old_condition_status' => $oldConditionStatus,
                    'new_condition_status' => $conditionStatus
                ]);
                
                // Only validate if condition_status is NOT changing
                // If condition_status is changing, we'll recalculate available_sqft in the update logic
                if (!$isConditionStatusChanging && $request->available_sqft > $totalSqft) {
                    \Log::warning('=== EARLY RETURN: Available sqft validation failed ===', [
                        'available_sqft' => $request->available_sqft,
                        'total_sqft' => $totalSqft
                    ]);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Available sqft cannot be greater than total sqft (' . number_format($totalSqft, 2) . ' sqft).');
                }
                
                if ($isConditionStatusChanging) {
                    \Log::info('=== STEP 3.3: Skipping available_sqft validation - condition_status is changing ===');
                }
            } else {
                \Log::info('=== STEP 3.3: Length/height not provided, skipping available_sqft validation ===');
            }
        } else {
            \Log::info('=== STEP 3.3: Available sqft validation skipped (block/monuments or not filled) ===');
        }
        \Log::info('=== STEP 3.3: Available sqft validation passed ===');
        \Log::info('=== STEP 4: All validations passed, entering try block ===');

        try {
            \Log::info('=== STEP 5: Inside try block ===');

            // Show what data will be updated
            \Log::info('=== STEP 6: Preparing update data ===');
            $updateData = $request->all();
            unset($updateData['_token'], $updateData['_method']);
            
            \Log::info('=== STEP 7: Update data prepared ===', [
                'update_data_keys' => array_keys($updateData),
                'condition_status_in_data' => isset($updateData['condition_status']),
                'condition_status_value' => $updateData['condition_status'] ?? 'NOT_SET'
            ]);
            
            // Debug: Log the request data
            \Log::info('=== STEP 8: Request data details ===', [
                'mine_vendor_id' => $request->mine_vendor_id,
                'product_id' => $request->product_id,
                'stone' => $request->stone,
                'condition_status' => $request->condition_status,
                'condition_status_received' => $request->has('condition_status'),
                'current_condition_status' => $stockAddition->condition_status,
                'all_request_keys' => array_keys($request->all())
            ]);
            
            // Debug: Check if stock has been issued
            \Log::info('Stock status check:', [
                'stock_id' => $stockAddition->id,
                'has_been_issued' => $stockAddition->hasBeenIssued(),
                'stock_issued_count' => $stockAddition->stockIssued()->count(),
                'current_mine_vendor_id' => $stockAddition->mine_vendor_id,
                'requested_mine_vendor_id' => $request->mine_vendor_id
            ]);
            
            // For issued stock, only block updates to quantity/dimension fields
            // Product Name, Mine Vendor, Particulars, and Condition Status can always be updated
            if ($stockAddition->hasBeenIssued()) {
                $quantityDimensionFields = ['length', 'height', 'size_3d', 'total_pieces', 'total_sqft', 'weight', 'available_pieces', 'available_sqft', 'available_weight'];
                
                // Check if any quantity/dimension fields are being changed
                $hasQuantityDimensionChanges = false;
                foreach ($quantityDimensionFields as $field) {
                    if ($request->has($field)) {
                        $oldValue = $stockAddition->getAttribute($field);
                        $newValue = $request->get($field);
                        
                        // Skip null values for sqft fields (form sends null but DB has 0.00)
                        if (($field === 'total_sqft' || $field === 'available_sqft') && $newValue === null && $oldValue == 0) {
                            continue;
                        }
                        
                        if ($newValue != $oldValue) {
                            $hasQuantityDimensionChanges = true;
                            \Log::info("Quantity/dimension field change detected: {$field}", [
                                'old_value' => $oldValue,
                                'new_value' => $newValue
                            ]);
                            break;
                        }
                    }
                }
                
                if ($hasQuantityDimensionChanges) {
                    \Log::info('Blocking update due to quantity/dimension field changes');
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Cannot update quantity or dimension fields for issued stock. Product Name, Mine Vendor, Particulars, and Condition Status can be updated freely.");
                }
                
                \Log::info('No quantity/dimension changes detected, allowing update of product/vendor/particulars/condition_status');
            }
            
            // Ensure condition_status is always included in update data - use exact value from request
            if ($request->has('condition_status')) {
                $updateData['condition_status'] = trim($request->condition_status);
                \Log::info('Condition status from request:', [
                    'raw_value' => $request->condition_status,
                    'trimmed_value' => trim($request->condition_status),
                    'current_db_value' => $stockAddition->condition_status
                ]);
            }
            
            // Handle NULL values based on condition status
            $conditionStatus = strtolower(trim($updateData['condition_status'] ?? ''));
            $oldConditionStatus = strtolower(trim($stockAddition->condition_status ?? ''));
            $isConditionStatusChange = $conditionStatus !== $oldConditionStatus;
            
            \Log::info('Condition status comparison:', [
                'request_condition_status' => $updateData['condition_status'] ?? 'NOT_SET',
                'current_db_condition_status' => $stockAddition->condition_status,
                'lowercase_request' => $conditionStatus,
                'lowercase_current' => $oldConditionStatus,
                'is_change' => $isConditionStatusChange
            ]);
            
            // Log condition status change
            if ($isConditionStatusChange) {
                \Log::info('Condition status change detected:', [
                    'old_status' => $stockAddition->condition_status,
                    'new_status' => $updateData['condition_status']
                ]);
            }
            
            // Preserve existing values when condition status doesn't change and stock is issued
            $preserveExisting = $stockAddition->hasBeenIssued() && !$isConditionStatusChange;
            
            if ($conditionStatus === 'block' || $conditionStatus === 'monuments') {
                // For Block/Monuments condition: only keep weight and total_pieces, set others to NULL
                // Always use condition_status directly from request to ensure it's the exact value
                $blockData = [
                    'product_id' => $updateData['product_id'],
                    'mine_vendor_id' => $updateData['mine_vendor_id'],
                    'stone' => $updateData['stone'],
                    'condition_status' => $request->has('condition_status') ? trim($request->condition_status) : $updateData['condition_status'],
                    'date' => $updateData['date'],
                    'weight' => !empty($updateData['weight']) ? $updateData['weight'] : ($preserveExisting ? $stockAddition->weight : null),
                    'total_pieces' => $updateData['total_pieces'],
                    
                    // Use submitted available_pieces if provided, otherwise use total_pieces
                    'available_pieces' => isset($updateData['available_pieces']) 
                        ? $updateData['available_pieces'] 
                        : ($preserveExisting ? $stockAddition->available_pieces : $updateData['total_pieces']),
                    
                    // Set dimension fields to NULL for Block/Monuments (unless preserving existing)
                    'length' => $preserveExisting ? $stockAddition->length : null,
                    'height' => $preserveExisting ? $stockAddition->height : null,
                    'diameter' => $preserveExisting ? $stockAddition->diameter : null,
                    'total_sqft' => $preserveExisting ? $stockAddition->total_sqft : null,
                    'available_sqft' => $preserveExisting ? $stockAddition->available_sqft : null,
                    'size_3d' => $preserveExisting ? $stockAddition->size_3d : null,
                    
                    // Use submitted available_weight if provided, otherwise calculate
                    'available_weight' => isset($updateData['available_weight']) 
                        ? $updateData['available_weight']
                        : ($preserveExisting ? $stockAddition->available_weight : (
                            !empty($updateData['weight']) && !empty($updateData['total_pieces']) 
                                ? ($updateData['weight'] * $updateData['total_pieces']) 
                                : 0
                        ))
                ];
                
                $updateData = $blockData;
            } else {
                // For other conditions: keep dimension fields, set weight to NULL
                // Always use condition_status directly from request to ensure it's the exact value
                $sizeData = [
                    'product_id' => $updateData['product_id'],
                    'mine_vendor_id' => $updateData['mine_vendor_id'],
                    'stone' => $updateData['stone'],
                    'condition_status' => $request->has('condition_status') ? trim($request->condition_status) : $updateData['condition_status'],
                    'date' => $updateData['date'],
                    'total_pieces' => $updateData['total_pieces'],
                    
                    // Use submitted available_pieces if provided, otherwise use total_pieces
                    'available_pieces' => isset($updateData['available_pieces']) 
                        ? $updateData['available_pieces'] 
                        : ($preserveExisting ? $stockAddition->available_pieces : $updateData['total_pieces']),
                    
                    'length' => !empty($updateData['length']) ? $updateData['length'] : ($preserveExisting ? $stockAddition->length : null),
                    'height' => !empty($updateData['height']) ? $updateData['height'] : ($preserveExisting ? $stockAddition->height : null),
                    'diameter' => !empty($updateData['diameter']) ? $updateData['diameter'] : ($preserveExisting ? $stockAddition->diameter : null),
                    
                    // Set weight to NULL for non-Block/Monuments conditions (unless preserving existing)
                    'weight' => $preserveExisting ? $stockAddition->weight : null,
                    'available_weight' => $preserveExisting ? $stockAddition->available_weight : 0
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
                        : ($preserveExisting ? $stockAddition->available_sqft : ($singlePieceSizeSqft * $totalPieces));
                } else {
                    // Set to NULL if no dimensions provided (or preserve existing)
                    $sizeData['total_sqft'] = $preserveExisting ? $stockAddition->total_sqft : null;
                    $sizeData['available_sqft'] = isset($updateData['available_sqft']) 
                        ? $updateData['available_sqft']
                        : ($preserveExisting ? $stockAddition->available_sqft : null);
                }
                
                $updateData = $sizeData;
            }
            
            \Log::info('Update data prepared:', $updateData);
            
            // Explicitly ensure condition_status is in update data - always use request value
            if ($request->has('condition_status')) {
                $updateData['condition_status'] = trim($request->condition_status);
                \Log::info('Condition status explicitly set in update data (final check):', [
                    'condition_status' => $updateData['condition_status'],
                    'request_value' => $request->condition_status,
                    'current_db_value' => $stockAddition->condition_status,
                    'will_change' => $updateData['condition_status'] !== $stockAddition->condition_status
                ]);
            } else {
                \Log::warning('Condition status NOT in request!', [
                    'update_data_has_it' => isset($updateData['condition_status']),
                    'update_data_value' => $updateData['condition_status'] ?? 'NOT_SET'
                ]);
            }
            
            // Debug: Check if mine_vendor_id is in update data
            \Log::info('Vendor update check:', [
                'mine_vendor_id_in_update_data' => isset($updateData['mine_vendor_id']),
                'mine_vendor_id_value' => $updateData['mine_vendor_id'] ?? 'NOT_SET',
                'condition_status_in_update_data' => isset($updateData['condition_status']),
                'condition_status_value' => $updateData['condition_status'] ?? 'NOT_SET',
                'update_data_keys' => array_keys($updateData)
            ]);
            
            // Log before update
            \Log::info('Before update - Stock ID: ' . $stockAddition->id);
            \Log::info('Before update - Current values:', [
                'mine_vendor_id' => $stockAddition->mine_vendor_id,
                'product_id' => $stockAddition->product_id,
                'condition_status' => $stockAddition->condition_status,
                'weight' => $stockAddition->weight,
                'total_pieces' => $stockAddition->total_pieces,
                'available_pieces' => $stockAddition->available_pieces,
                'available_weight' => $stockAddition->available_weight
            ]);
            
            // Debug: Check if update was successful
            \Log::info('Update method called', [
                'update_data_keys' => array_keys($updateData),
                'condition_status_in_update' => isset($updateData['condition_status']),
                'condition_status_value' => $updateData['condition_status'] ?? 'NOT_SET',
                'current_condition_status' => $stockAddition->condition_status,
                'about_to_update' => true
            ]);
            
            // Log condition_status before update
            $oldConditionStatus = $stockAddition->condition_status;
            $newConditionStatus = $updateData['condition_status'] ?? null;
            
            \Log::info('About to update condition_status:', [
                'old_value' => $oldConditionStatus,
                'new_value' => $newConditionStatus,
                'will_change' => $oldConditionStatus !== $newConditionStatus
            ]);
            
            // Use update() method which handles mass assignment properly
            // But first manually set condition_status to ensure it's included
            if (isset($updateData['condition_status'])) {
                $stockAddition->condition_status = $updateData['condition_status'];
            }
            
            // Use fill() and save() to ensure all fields are updated
            $stockAddition->fill($updateData);
            
            // Force condition_status to be set
            if (isset($updateData['condition_status'])) {
                $stockAddition->condition_status = trim($updateData['condition_status']);
            }
            
            // Check if condition_status is dirty
            if ($stockAddition->isDirty('condition_status')) {
                \Log::info('Condition status IS dirty - will be saved', [
                    'old' => $stockAddition->getOriginal('condition_status'),
                    'new' => $stockAddition->getAttribute('condition_status')
                ]);
            } else {
                \Log::warning('Condition status NOT dirty after fill!', [
                    'original' => $stockAddition->getOriginal('condition_status'),
                    'current' => $stockAddition->getAttribute('condition_status'),
                    'update_data_value' => $updateData['condition_status'] ?? 'NOT_SET',
                    'are_equal' => $stockAddition->getOriginal('condition_status') === $stockAddition->getAttribute('condition_status')
                ]);
                // Force it to be dirty if values are different
                if ($oldConditionStatus !== $newConditionStatus && $newConditionStatus !== null) {
                    $stockAddition->condition_status = $newConditionStatus;
                    \Log::info('Force set condition_status to make it dirty');
                }
            }
            
            // Try saving
            $updateResult = $stockAddition->save();
            
            // If condition_status didn't update via save(), try direct DB update as fallback
            if (isset($updateData['condition_status']) && $oldConditionStatus !== $newConditionStatus) {
                // Check if it actually changed
                if (!$stockAddition->wasChanged('condition_status')) {
                    \Log::warning('Condition status was NOT changed by save(), using direct DB update');
                    $dbUpdated = DB::table('stock_additions')
                        ->where('id', $stockAddition->id)
                        ->update(['condition_status' => trim($updateData['condition_status'])]);
                    
                    \Log::info('Direct DB update result:', [
                        'db_update_result' => $dbUpdated,
                        'condition_status_set_to' => trim($updateData['condition_status']),
                        'rows_affected' => $dbUpdated
                    ]);
                    
                    // Refresh to get updated value
                    $stockAddition->refresh();
                } else {
                    \Log::info('Condition status was successfully changed by save()');
                }
            }
            
            \Log::info('Update result:', [
                'update_successful' => $updateResult,
                'update_data_keys' => array_keys($updateData),
                'dirty_fields_after_save' => array_keys($stockAddition->getDirty()),
                'condition_status_was_changed' => $stockAddition->wasChanged('condition_status'),
                'condition_status_after_save' => $stockAddition->condition_status,
                'condition_status_changed_from' => $oldConditionStatus,
                'condition_status_changed_to' => $stockAddition->condition_status
            ]);
            
            // Refresh and log after update
            $stockAddition->refresh();
            \Log::info('After update - Updated values:', [
                'mine_vendor_id' => $stockAddition->mine_vendor_id,
                'product_id' => $stockAddition->product_id,
                'condition_status' => $stockAddition->condition_status,
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
