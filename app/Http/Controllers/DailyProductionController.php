<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use App\Models\StockAddition;
use App\Models\StockIssued;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use App\Models\Machine;
use App\Models\Operator;
use App\Models\ConditionStatus;
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
            $query->whereHas('items', function ($itemQuery) use ($request) {
                $itemQuery->where('condition_status', $request->get('condition_status'));
            });
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
                $query->join('daily_production_items', 'daily_production.id', '=', 'daily_production_items.daily_production_id')
                      ->orderBy('daily_production_items.condition_status', $sortOrder)
                      ->select('daily_production.*');
                break;
            default:
                $query->orderBy('date', $sortOrder);
                break;
        }

        $dailyProduction = $query->with(['stockAddition.product', 'stockAddition.mineVendor', 'items', 'machine', 'operator'])->paginate(15)->withQueryString();

        // Get filter options
        $products = \App\Models\Product::orderBy('name')->get();
        $vendors = \App\Models\MineVendor::orderBy('name')->get();
        $machines = DailyProduction::distinct()->pluck('machine_name')->filter()->sort()->values();
        $operators = DailyProduction::distinct()->pluck('operator_name')->filter()->sort()->values();
        $conditionStatuses = \App\Models\DailyProductionItem::distinct()->pluck('condition_status')->filter()->sort()->values();

        return view('stock-management.daily-production.index', compact('dailyProduction', 'products', 'vendors', 'machines', 'operators', 'conditionStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $stockIssuedId = $request->get('stock_issued_id');
        $stockIssued = null;

        if ($stockIssuedId) {
            $stockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
                ->findOrFail($stockIssuedId);
        }

        // Get stock issued records for production (exclude those with closed daily productions)
        $availableStockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor', 'machine', 'operator'])
            ->whereDoesntHave('dailyProduction', function ($query) {
                $query->where('status', 'closed');
            })
            ->orderBy('date', 'desc')
            ->get();

        // Get recent machine and operator names for auto-fill suggestions
        $recentMachines = DailyProduction::whereNotNull('machine_name')
            ->distinct()
            ->pluck('machine_name')
            ->take(10)
            ->filter()
            ->values();

        $recentOperators = DailyProduction::whereNotNull('operator_name')
            ->distinct()
            ->pluck('operator_name')
            ->take(10)
            ->filter()
            ->values();

        // Get active machines and operators for dropdowns
        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        // Get condition statuses from database
        $conditionStatuses = ConditionStatus::active()->orderBy('name')->get();

        return view('stock-management.daily-production.create', compact('stockIssued', 'availableStockIssued', 'recentMachines', 'recentOperators', 'machines', 'operators', 'conditionStatuses'));
    }

    /**
     * Show the Excel-style form for creating multiple daily production records.
     */
    public function createExcel(Request $request)
    {
        // Get available stock issued for production
        $availableStockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor', 'machine', 'operator'])
            ->whereDoesntHave('dailyProduction', function ($query) {
                $query->where('status', 'closed');
            })
            ->orderBy('date', 'desc')
            ->get();

        // Get active machines and operators
        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();
        $conditionStatuses = ConditionStatus::active()->orderBy('name')->get();

        return view('stock-management.daily-production.create-excel', compact('availableStockIssued', 'machines', 'operators', 'conditionStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Store method called', [
                'request_data' => $request->all()
            ]);
            
        $request->validate([
            'stock_issued_id' => 'required|exists:stock_issued,id',
            'machine_name' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'stone' => 'nullable|string|max:255',
            'date' => 'required|date',
            'status' => 'required|in:open,closed',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.size' => 'nullable|string|max:255',
            'items.*.diameter' => 'nullable|string|max:255',
            'items.*.condition_status' => 'required|string|max:255',
            'items.*.special_status' => 'nullable|string|max:255',
            'items.*.total_pieces' => 'required|integer|min:1',
                'items.*.total_sqft' => 'nullable|numeric|min:0',
            'items.*.total_weight' => 'nullable|numeric|min:0',
            'items.*.narration' => 'nullable|string',
        ]);
            
            // Additional conditional validation based on condition status
            foreach ($request->items as $index => $item) {
                $isBlockOrMonuments = in_array(strtolower($item['condition_status'] ?? ''), ['block', 'monuments']);
                
                if ($isBlockOrMonuments) {
                    // For block/monuments, total_weight is required
                    if (empty($item['total_weight'])) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(["items.{$index}.total_weight" => 'Total weight is required for block or monuments production.']);
                    }
                } else {
                    // For slabs/other products, total_sqft is required
                    if (empty($item['total_sqft'])) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(["items.{$index}.total_sqft" => 'Total sqft is required for slab production.']);
                    }
                }
            }
            
            \Log::info('Validation passed');

        $stockIssued = StockIssued::with('stockAddition')->findOrFail($request->stock_issued_id);

        // Calculate total pieces and measurement from all items (handle null values)
        $totalPieces = collect($request->items)->sum('total_pieces');
        $totalSqft = collect($request->items)->sum(function($item) {
            return is_numeric($item['total_sqft']) ? $item['total_sqft'] : 0;
        });
        $totalWeight = collect($request->items)->sum(function($item) {
            return is_numeric($item['total_weight']) ? $item['total_weight'] : 0;
        });
        
        // Check if this is a block/monuments or sqft-based product
        $isBlockOrMonuments = in_array(strtolower($stockIssued->stockAddition->condition_status), ['block', 'monuments']);
        
        if ($isBlockOrMonuments) {
            // For block/monuments: validate against issued weight
            $issuedWeight = $stockIssued->weight_issued;
            $measurementType = 'weight';
            $measurementUnit = 'kg';
            
            if ($totalWeight > $issuedWeight) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Total production weight ({$totalWeight} kg) cannot exceed issued weight ({$issuedWeight} kg).");
            }
            
            // Calculate wastage (issued weight - produced weight)
            $wastageWeight = $issuedWeight - $totalWeight;
            $wastageSqft = 0;
        } else {
            // For slabs/other products: validate against issued sqft
            $issuedSqft = $stockIssued->sqft_issued;
            $measurementType = 'sqft';
            $measurementUnit = 'sqft';
            
            if ($totalSqft > $issuedSqft) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Total production sqft ({$totalSqft}) cannot exceed issued sqft ({$issuedSqft}).");
            }
            
            // Calculate wastage (issued sqft - produced sqft)
            $wastageSqft = $issuedSqft - $totalSqft;
            $wastageWeight = 0;
        }

        // Convert date string to Carbon instance
        $date = \Carbon\Carbon::parse($request->date);

        // Create daily production record
        $dailyProduction = DailyProduction::create([
            'stock_addition_id' => $stockIssued->stock_addition_id,
            'stock_issued_id' => $request->stock_issued_id,
            'machine_name' => $request->machine_name,
            'operator_name' => $request->operator_name,
            'notes' => $request->notes,
            'stone' => $stockIssued->stone ?? $stockIssued->stockAddition->stone,
            'date' => $date,
            'status' => $request->status,
            'wastage_sqft' => $wastageSqft, // Store sqft wastage for sqft products
        ]);

        // Process production items with product matching logic
        $processedItems = [];

        foreach ($request->items as $itemData) {
            $productKey = $this->generateProductKey(
                $itemData['product_name'],
                $itemData['size'] ?? null,
                $itemData['diameter'] ?? null,
                $itemData['condition_status'],
                $itemData['special_status'] ?? null
            );

            // Ensure null values are converted to 0 before processing
            $itemData['total_sqft'] = $itemData['total_sqft'] ?? 0;
            $itemData['total_weight'] = $itemData['total_weight'] ?? 0;

            // Check if similar product already exists in processed items
            if (isset($processedItems[$productKey])) {
                // Merge quantities
                $processedItems[$productKey]['total_pieces'] += $itemData['total_pieces'];
                $processedItems[$productKey]['total_sqft'] += $itemData['total_sqft'];
                $processedItems[$productKey]['total_weight'] += $itemData['total_weight'];

                // Merge narration if provided
                if (!empty($itemData['narration'])) {
                    $processedItems[$productKey]['narration'] =
                        $processedItems[$productKey]['narration'] . '; ' . $itemData['narration'];
                }
            } else {
                // Add new item
                $processedItems[$productKey] = $itemData;
            }
        }

        // Create production items
        foreach ($processedItems as $itemData) {
            // Values are already sanitized above
            
            $itemData['stock_addition_id'] = $stockIssued->stock_addition_id;
            $dailyProduction->items()->create($itemData);
        }

        // Generate accounting journal entry
        $this->generateAccountingEntry($dailyProduction);

        $measurementText = $isBlockOrMonuments ? "{$totalWeight} kg" : "{$totalSqft} sqft";
        return redirect()->route('stock-management.daily-production.index')
            ->with('success', "Daily production recorded successfully with {$totalPieces} pieces ({$measurementText}) across " . count($processedItems) . ' product(s).');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in store', [
                'errors' => $e->errors()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error in store method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the production: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple daily production records from Excel-style form.
     */
    public function storeMultiple(Request $request)
    {
        // Validate common fields
        $request->validate([
            'date' => 'required|date',
            'machine_name' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:open,closed',
            'productions' => 'required|array|min:1',
            'productions.*.stock_issued_id' => 'required|exists:stock_issued,id',
            'productions.*.product_name' => 'required|string|max:255',
            'productions.*.weight' => 'nullable|numeric|min:0',
            'productions.*.size' => 'nullable|string|max:255',
            'productions.*.diameter' => 'nullable|string|max:255',
            'productions.*.condition_status' => 'required|string|max:255',
            'productions.*.special_status' => 'nullable|string|max:255',
            'productions.*.total_pieces' => 'required|integer|min:1',
            'productions.*.total_sqft' => 'required|numeric|min:0',
            'productions.*.total_weight' => 'nullable|numeric|min:0',
            'productions.*.narration' => 'nullable|string',
        ]);

        $createdCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            foreach ($request->productions as $index => $productionData) {
                // Skip empty rows
                if (empty($productionData['stock_issued_id']) || empty($productionData['product_name']) || 
                    empty($productionData['condition_status']) || empty($productionData['total_pieces'])) {
                    continue;
                }

                $stockIssued = StockIssued::with('stockAddition')->findOrFail($productionData['stock_issued_id']);
                
                // Check if this is a block/monuments or sqft-based product
                $isBlockOrMonuments = in_array(strtolower($stockIssued->stockAddition->condition_status), ['block', 'monuments']);
                
                if ($isBlockOrMonuments) {
                    // For block/monuments: validate against issued weight
                    $issuedWeight = $stockIssued->weight_issued;
                    $totalWeight = floatval($productionData['total_weight'] ?? 0);
                    
                    if ($totalWeight > $issuedWeight) {
                        $errors[] = "Row " . ($index + 1) . ": Total production weight ({$totalWeight} kg) cannot exceed issued weight ({$issuedWeight} kg).";
                        continue;
                    }
                } else {
                    // For sqft-based products: validate against issued sqft
                    $issuedSqft = $stockIssued->sqft_issued;
                    $totalSqft = floatval($productionData['total_sqft'] ?? 0);
                    
                    if ($totalSqft > $issuedSqft) {
                        $errors[] = "Row " . ($index + 1) . ": Total production sqft ({$totalSqft} sqft) cannot exceed issued sqft ({$issuedSqft} sqft).";
                        continue;
                    }
                }

                // Calculate wastage for this production
                $isBlockOrMonuments = in_array(strtolower($stockIssued->stockAddition->condition_status), ['block', 'monuments']);
                if ($isBlockOrMonuments) {
                    // For blocks/monuments: wastage is in weight, sqft wastage is 0
                    $wastageSqft = 0;
                } else {
                    // For slabs/other products: calculate sqft wastage
                    $issuedSqft = $stockIssued->sqft_issued;
                    $wastageSqft = $issuedSqft - $totalSqft;
                }

                // Create daily production record
                $dailyProduction = DailyProduction::create([
                    'stock_issued_id' => $productionData['stock_issued_id'],
                    'machine_name' => $request->machine_name,
                    'operator_name' => $request->operator_name,
                    'notes' => $request->notes,
                    'stone' => $stockIssued->stockAddition->stone,
                    'date' => $request->date,
                    'status' => $request->status,
                    'wastage_sqft' => $wastageSqft,
                ]);

                // Create production item
                $dailyProduction->items()->create([
                    'product_name' => $productionData['product_name'],
                    'weight' => floatval($productionData['weight'] ?? 0),
                    'stock_addition_id' => $stockIssued->stock_addition_id,
                    'size' => $productionData['size'] ?? null,
                    'diameter' => $productionData['diameter'] ?? null,
                    'condition_status' => $productionData['condition_status'],
                    'special_status' => $productionData['special_status'] ?? null,
                    'total_pieces' => intval($productionData['total_pieces']),
                    'total_sqft' => floatval($productionData['total_sqft']),
                    'total_weight' => floatval($productionData['total_weight'] ?? 0),
                    'narration' => $productionData['narration'] ?? null,
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

            return redirect()->route('stock-management.daily-production.index')
                ->with('success', "Successfully created {$createdCount} daily production record(s).");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Multiple daily production creation error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create daily production records. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyProduction $dailyProduction)
    {
        $dailyProduction->load(['stockAddition.product', 'stockAddition.mineVendor', 'items.stockAddition', 'machine', 'operator']);
        
        // Get produced stock additions
        $producedStockAdditions = $dailyProduction->producedStockAdditions();

        return view('stock-management.daily-production.show', compact('dailyProduction', 'producedStockAdditions'));
    }

    /**
     * Print the daily production details.
     */
    public function print(DailyProduction $dailyProduction)
    {
        $dailyProduction->load(['stockAddition.product', 'stockAddition.mineVendor', 'items.stockAddition', 'machine', 'operator']);
        
        // Get produced stock additions
        $producedStockAdditions = $dailyProduction->producedStockAdditions();

        return view('stock-management.daily-production.print', compact('dailyProduction', 'producedStockAdditions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyProduction $dailyProduction)
    {
        // Check if production is closed - prevent editing
        if ($dailyProduction->isClosed()) {
            return redirect()->route('stock-management.daily-production.show', $dailyProduction)
                ->with('error', 'Cannot edit closed production. Production is marked as completed.');
        }

        // Load the daily production with its relationships
        $dailyProduction->load(['stockAddition.product', 'stockAddition.mineVendor', 'stockIssued', 'items']);
        
        // Get stock issued records for production
        // Exclude stock issued items used in closed productions, but include the current one
        $availableStockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
            ->where(function ($query) use ($dailyProduction) {
                $query->whereDoesntHave('dailyProduction', function ($dailyQuery) {
                    $dailyQuery->where('status', 'closed');
                })
                ->orWhere('id', $dailyProduction->stock_issued_id); // Include current stock issued
            })
            ->orderBy('date', 'desc')
            ->get();

        // Get active machines and operators for dropdowns
        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        // Get condition statuses from database
        $conditionStatuses = ConditionStatus::active()->orderBy('name')->get();
        return view('stock-management.daily-production.edit', compact('dailyProduction', 'availableStockIssued', 'machines', 'operators', 'conditionStatuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyProduction $dailyProduction)
    {
        \Log::info('Update method called', [
            'production_id' => $dailyProduction->id,
            'request_data' => $request->all()
        ]);
        
        // Check if production is closed - prevent updating
        if ($dailyProduction->isClosed()) {
            return redirect()->route('stock-management.daily-production.show', $dailyProduction)
                ->with('error', 'Cannot update closed production. Production is marked as completed.');
        }

        try {
            // Custom validation based on condition status
            foreach ($request->items as $index => $item) {
                $conditionStatus = $item['condition_status'] ?? '';
                $isBlock = in_array(strtolower($conditionStatus), ['block', 'monuments']);
                
                if ($isBlock) {
                    // For block/monuments: require total_weight, make total_sqft nullable
                    $request->merge(["items.{$index}.total_weight" => $item['total_weight'] ?? 0]);
                } else {
                    // For slabs/others: require total_sqft, make total_weight nullable
                    $request->merge(["items.{$index}.total_sqft" => $item['total_sqft'] ?? 0]);
                }
            }
            
            $request->validate([
            'stock_issued_id' => 'required|exists:stock_issued,id',
            'machine_name' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'stone' => 'nullable|string|max:255',
            'date' => 'required|date',
            'status' => 'required|in:open,closed',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.size' => 'nullable|string|max:255',
            'items.*.diameter' => 'nullable|string|max:255',
            'items.*.condition_status' => 'required|string|max:255',
            'items.*.special_status' => 'nullable|string|max:255',
            'items.*.total_pieces' => 'required|integer|min:1',
            'items.*.total_sqft' => 'nullable|numeric|min:0',
            'items.*.total_weight' => 'nullable|numeric|min:0',
            'items.*.narration' => 'nullable|string',
        ]);

        $stockIssued = StockIssued::with('stockAddition')->findOrFail($request->stock_issued_id);

        // Calculate total pieces and measurement from all items (handle null values)
        $totalPieces = collect($request->items)->sum('total_pieces');
        $totalSqft = collect($request->items)->sum(function($item) {
            return is_numeric($item['total_sqft']) ? $item['total_sqft'] : 0;
        });
        $totalWeight = collect($request->items)->sum(function($item) {
            return is_numeric($item['total_weight']) ? $item['total_weight'] : 0;
        });
        
        // Check if this is a block/monuments or sqft-based product
        $isBlockOrMonuments = in_array(strtolower($stockIssued->stockAddition->condition_status), ['block', 'monuments']);
        
        if ($isBlockOrMonuments) {
            // For block/monuments: validate against issued weight
            $issuedWeight = $stockIssued->weight_issued;
            $measurementType = 'weight';
            $measurementUnit = 'kg';
            
            if ($totalWeight > $issuedWeight) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Total production weight ({$totalWeight} kg) cannot exceed issued weight ({$issuedWeight} kg).");
            }
            
            // Calculate wastage (issued weight - produced weight)
            $wastageWeight = $issuedWeight - $totalWeight;
            $wastageSqft = 0;
        } else {
            // For slabs/other products: validate against issued sqft
            $issuedSqft = $stockIssued->sqft_issued;
            $measurementType = 'sqft';
            $measurementUnit = 'sqft';
            
            if ($totalSqft > $issuedSqft) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Total production sqft ({$totalSqft}) cannot exceed issued sqft ({$issuedSqft}).");
            }
            
            // Calculate wastage (issued sqft - produced sqft)
            $wastageSqft = $issuedSqft - $totalSqft;
            $wastageWeight = 0;
        }

        // Store old production data for wastage calculation

        // Convert date string to Carbon instance
        $date = \Carbon\Carbon::parse($request->date);

        // Update daily production record
        $dailyProduction->update([
            'stock_addition_id' => $stockIssued->stock_addition_id,
            'stock_issued_id' => $request->stock_issued_id,
            'machine_name' => $request->machine_name,
            'operator_name' => $request->operator_name,
            'notes' => $request->notes,
            'stone' => $stockIssued->stone ?? $stockIssued->stockAddition->stone,
            'date' => $date,
            'status' => $request->status,
            'wastage_sqft' => $wastageSqft,
            'wastage_weight' => $wastageWeight,
        ]);

        // Delete existing production items
        $dailyProduction->items()->delete();

        // Process production items with product matching logic
        $processedItems = [];

        foreach ($request->items as $itemData) {
            $productKey = $this->generateProductKey(
                $itemData['product_name'],
                $itemData['size'] ?? null,
                $itemData['diameter'] ?? null,
                $itemData['condition_status'],
                $itemData['special_status'] ?? null
            );

            // Ensure null values are converted to 0 before processing
            $itemData['total_sqft'] = $itemData['total_sqft'] ?? 0;
            $itemData['total_weight'] = $itemData['total_weight'] ?? 0;

            // Check if similar product already exists in processed items
            if (isset($processedItems[$productKey])) {
                // Merge quantities
                $processedItems[$productKey]['total_pieces'] += $itemData['total_pieces'];
                $processedItems[$productKey]['total_sqft'] += $itemData['total_sqft'];
                $processedItems[$productKey]['total_weight'] += $itemData['total_weight'];

                // Merge narration if provided
                if (!empty($itemData['narration'])) {
                    $processedItems[$productKey]['narration'] =
                        $processedItems[$productKey]['narration'] . '; ' . $itemData['narration'];
                }
            } else {
                // Add new item
                $processedItems[$productKey] = $itemData;
            }
        }

        // Create new production items
        foreach ($processedItems as $itemData) {
            $itemData['stock_addition_id'] = $stockIssued->stock_addition_id;
            $dailyProduction->items()->create($itemData);
        }

        // Log the production update
        \Log::info("Daily Production Updated", [
            'daily_production_id' => $dailyProduction->id,
            'total_pieces' => $totalPieces,
            'total_sqft' => $totalSqft,
            'total_weight' => $totalWeight,
            'wastage_sqft' => $wastageSqft,
            'wastage_weight' => $wastageWeight
        ]);

            $measurementText = $isBlockOrMonuments ? "{$totalWeight} kg" : "{$totalSqft} sqft";
            return redirect()->route('stock-management.daily-production.index')
                ->with('success', "Daily production updated successfully with {$totalPieces} pieces ({$measurementText}) across " . count($processedItems) . " product(s).");
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed in update', [
                'errors' => $e->errors()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating daily production', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the production: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyProduction $dailyProduction)
    {
        // Check if production is closed - prevent deletion
        if ($dailyProduction->isClosed()) {
            return redirect()->route('stock-management.daily-production.index')
                ->with('error', 'Cannot delete closed production. Production is marked as completed.');
        }

        $dailyProduction->delete();

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production deleted successfully.');
    }

    /**
     * Close the daily production.
     */
    public function close(DailyProduction $dailyProduction)
    {
        try {
            if ($dailyProduction->isClosed()) {
                return redirect()->route('stock-management.daily-production.index')
                    ->with('error', 'Production is already closed.');
            }

            // Load relationships
            $dailyProduction->load(['items', 'stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);
            
            // Get the original stock addition
            $originalStockAddition = $dailyProduction->stockIssued->stockAddition;

            // Check if machine can add stock (case-insensitive search)
            $machine = \App\Models\Machine::whereRaw('LOWER(name) = LOWER(?)', [$dailyProduction->machine_name])->first();
            
            \Log::info("Machine check before closing production", [
                'machine_name' => $dailyProduction->machine_name,
                'machine_found' => $machine ? 'yes' : 'no',
                'can_add_stock' => $machine ? ($machine->can_add_stock ? 'true' : 'false') : 'N/A',
                'items_count' => $dailyProduction->items->count(),
                'items_details' => $dailyProduction->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'condition_status' => $item->condition_status,
                        'total_pieces' => $item->total_pieces,
                        'total_sqft' => $item->total_sqft,
                        'total_weight' => $item->total_weight
                    ];
                })
            ]);
            
            $stockAdditionsCreated = 0;
            $firstStockAdditionId = null;
            
            if ($machine && $machine->can_add_stock) {
                // Create a UNIQUE stock addition for EACH production item
                foreach ($dailyProduction->items as $item) {
                    // Determine if this is a weight-based product
                    $isBlockOrMonuments = in_array(strtolower($item->condition_status ?? ''), ['block', 'monuments']);
                    
                    // Split size if present
                    $sizeParts = explode('*', $item->size ?? '1');
                    
                    // Create UNIQUE stock addition for this specific production item
                    $newStockAddition = \App\Models\StockAddition::create([
                        'product_id' => $originalStockAddition->product_id,
                        'mine_vendor_id' => $originalStockAddition->mine_vendor_id,
                        'stone' => $item->product_name,
                        'diameter' => $item->diameter ?? null,
                        'length' => $sizeParts[0] ?? null,
                        'height' => $sizeParts[1] ?? null,
                        'total_pieces' => $item->total_pieces,
                        'total_sqft' => $isBlockOrMonuments ? null : $item->total_sqft,
                        'weight' => $isBlockOrMonuments ? $item->total_weight : null,
                        'total_weight' => $isBlockOrMonuments ? $item->total_weight : null,
                        'available_pieces' => $item->total_pieces,
                        'available_sqft' => $isBlockOrMonuments ? null : $item->total_sqft,
                        'available_weight' => $isBlockOrMonuments ? $item->total_weight : 0,
                        'condition_status' => $item->condition_status,
                        'date' => $dailyProduction->date,
                    ]);
                    
                    // Update this production item to link it to the newly created stock addition
                    // Note: we OVERWRITE the stock_addition_id from the original (input) to the produced (output)
                    $item->stock_addition_id = $newStockAddition->id;
                    $item->save();
                    
                    // Store the first created stock addition ID for the daily production
                    if ($firstStockAdditionId === null) {
                        $firstStockAdditionId = $newStockAddition->id;
                    }
                    
                    $stockAdditionsCreated++;
                    
                    \Log::info("Created stock addition for production item", [
                        'production_item_id' => $item->id,
                        'stock_addition_id' => $newStockAddition->id,
                        'product_name' => $item->product_name,
                        'total_pieces' => $item->total_pieces
                    ]);
                }
                
                // Update the daily production with the first produced stock addition ID
                if ($firstStockAdditionId) {
                    $dailyProduction->produced_stock_addition_id = $firstStockAdditionId;
                    $dailyProduction->save();
                }
                
                \Log::info("Stock additions created when closing production", [
                    'daily_production_id' => $dailyProduction->id,
                    'stock_additions_created' => $stockAdditionsCreated,
                    'total_items' => $dailyProduction->items->count()
                ]);
            } else {
                \Log::warning("Stock additions not created - Machine cannot add stock", [
                    'machine_name' => $dailyProduction->machine_name,
                    'can_add_stock' => $machine ? $machine->can_add_stock : 'Machine not found'
                ]);
            }

            // Close the production (this will update status)
            $dailyProduction->close();

            $message = $stockAdditionsCreated > 0 
                ? "Production closed successfully. {$stockAdditionsCreated} stock addition(s) created for produced items."
                : "Production closed successfully.";

            return redirect()->route('stock-management.daily-production.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Error closing production', [
                'daily_production_id' => $dailyProduction->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('stock-management.daily-production.index')
                ->with('error', 'Failed to close production: ' . $e->getMessage());
        }
    }

    /**
     * Open the daily production.
     */
    public function open(DailyProduction $dailyProduction)
    {
        try {
            if ($dailyProduction->isOpen()) {
                return redirect()->route('stock-management.daily-production.index')
                    ->with('error', 'Production is already open.');
            }

            $dailyProduction->open();

            return redirect()->route('stock-management.daily-production.index')
                ->with('success', 'Production opened successfully.');
        } catch (\Exception $e) {
            return redirect()->route('stock-management.daily-production.index')
                ->with('error', 'Failed to open production: ' . $e->getMessage());
        }
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

    /**
     * Generate accounting journal entry for daily production.
     */
    private function generateAccountingEntry(DailyProduction $dailyProduction)
    {
        try {
            // Get accounts
            $finishedGoodsAccount = ChartOfAccount::where('account_code', '1160')->first(); // Finished Goods
            $wipAccount = ChartOfAccount::where('account_code', '1150')->first(); // Work in Progress

            if (!$finishedGoodsAccount || !$wipAccount) {
                \Log::warning('Required accounts not found for daily production accounting entry');
                return;
            }

            // Calculate cost (you might want to add cost fields)
            $costPerSqft = 100; // This should come from your data
            $totalCost = $dailyProduction->total_sqft * $costPerSqft;

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_date' => $dailyProduction->date,
                'description' => "Production completed: {$dailyProduction->product} on {$dailyProduction->machine_name}",
                'entry_type' => 'AUTO_PRODUCTION',
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'notes' => "Auto-generated for daily production #{$dailyProduction->id}"
            ]);

            // Create transactions
            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $finishedGoodsAccount->id,
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'description' => "Finished goods: {$dailyProduction->product}",
                'reference_type' => 'daily_production',
                'reference_id' => $dailyProduction->id
            ]);

            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $wipAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'description' => "Work in progress decrease: {$dailyProduction->product}",
                'reference_type' => 'daily_production',
                'reference_id' => $dailyProduction->id
            ]);

            \Log::info("Accounting entry created for daily production #{$dailyProduction->id}");

        } catch (\Exception $e) {
            \Log::error("Failed to create accounting entry for daily production #{$dailyProduction->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate product key for matching similar products.
     */
    private function generateProductKey(string $productName, ?string $size, ?string $diameter, string $conditionStatus, ?string $specialStatus): string
    {
        return strtolower(trim(
            $productName . '|' .
            ($size ?? '') . '|' .
            ($diameter ?? '') . '|' .
            $conditionStatus . '|' .
            ($specialStatus ?? '')
        ));
    }
}
