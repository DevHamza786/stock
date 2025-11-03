<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use App\Models\GatePass;
use App\Models\GatePassItem;
use App\Models\JournalEntry;
use App\Models\StockAddition;
use App\Models\StockIssued;
use App\Models\StockLog;
use App\Models\Machine;
use App\Models\Operator;
use Illuminate\Http\Request;

class GatePassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GatePass::with(['items.stockAddition.product', 'items.stockAddition.mineVendor', 'stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('client_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('items.stockAddition.product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.stockAddition.mineVendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.stockAddition', function ($stockQuery) use ($search) {
                        $stockQuery->where('stone', 'like', "%{$search}%")
                            ->orWhere('size_3d', 'like', "%{$search}%")
                            ->orWhere('condition_status', 'like', "%{$search}%");
                    })
                    ->orWhereHas('stockIssued.stockAddition.product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('stockIssued.stockAddition.mineVendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('stockIssued.stockAddition', function ($stockQuery) use ($search) {
                        $stockQuery->where('stone', 'like', "%{$search}%")
                            ->orWhere('size_3d', 'like', "%{$search}%")
                            ->orWhere('condition_status', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->whereHas('stockIssued.stockAddition.product', function ($productQuery) use ($request) {
                $productQuery->where('id', $request->get('product_id'));
            });
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->whereHas('stockIssued.stockAddition.mineVendor', function ($vendorQuery) use ($request) {
                $vendorQuery->where('id', $request->get('vendor_id'));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by destination
        if ($request->filled('destination')) {
            $query->where('destination', 'like', "%{$request->get('destination')}%");
        }

        // Filter by vehicle number
        if ($request->filled('vehicle_number')) {
            $query->where('vehicle_number', 'like', "%{$request->get('vehicle_number')}%");
        }

        // Filter by driver name
        if ($request->filled('driver_name')) {
            $query->where('driver_name', 'like', "%{$request->get('driver_name')}%");
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
                $query->join('stock_issued', 'gate_pass.stock_issued_id', '=', 'stock_issued.id')
                    ->join('stock_additions', 'stock_issued.stock_addition_id', '=', 'stock_additions.id')
                    ->join('products', 'stock_additions.product_id', '=', 'products.id')
                    ->orderBy('products.name', $sortOrder)
                    ->select('gate_pass.*');
                break;
            case 'vendor':
                $query->join('stock_issued', 'gate_pass.stock_issued_id', '=', 'stock_issued.id')
                    ->join('stock_additions', 'stock_issued.stock_addition_id', '=', 'stock_additions.id')
                    ->join('mine_vendors', 'stock_additions.mine_vendor_id', '=', 'mine_vendors.id')
                    ->orderBy('mine_vendors.name', $sortOrder)
                    ->select('gate_pass.*');
                break;
            case 'destination':
                $query->orderBy('destination', $sortOrder);
                break;
            case 'vehicle':
                $query->orderBy('vehicle_number', $sortOrder);
                break;
            case 'driver':
                $query->orderBy('driver_name', $sortOrder);
                break;
            case 'quantity':
                // Sort by total quantity from items
                $query->withCount('items as total_quantity')->orderBy('total_quantity', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            default:
                $query->orderBy('date', $sortOrder);
                break;
        }

        $gatePasses = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = \App\Models\Product::orderBy('name')->get();
        $vendors = \App\Models\MineVendor::orderBy('name')->get();
        $statuses = ['Pending', 'Approved', 'Completed'];
        $destinations = GatePass::distinct()->pluck('destination')->filter()->sort()->values();
        $vehicles = GatePass::distinct()->pluck('vehicle_number')->filter()->sort()->values();
        $drivers = GatePass::distinct()->pluck('driver_name')->filter()->sort()->values();

        return view('stock-management.gate-pass.index', compact('gatePasses', 'products', 'vendors', 'statuses', 'destinations', 'vehicles', 'drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $stockAdditionId = $request->get('stock_addition_id');
        $selectedStockAddition = null;

        if ($stockAdditionId) {
            $selectedStockAddition = StockAddition::with(['product', 'mineVendor'])
                ->findOrFail($stockAdditionId);
        }

        // Get stock additions that have available stock (not fully issued)
        $stockAdditions = StockAddition::with(['product', 'mineVendor', 'stockIssued'])
            ->where('available_pieces', '>', 0)
            ->whereHas('product')
            ->whereHas('mineVendor')
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function ($stockAddition) {
                // Double-check that stock actually has available pieces
                return $stockAddition->hasAvailableStock();
            });

        // Get active machines and operators for dropdowns
        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        return view('stock-management.gate-pass.create', compact('selectedStockAddition', 'stockAdditions', 'machines', 'operators'));
    }

    /**
     * Show the Excel-style form for creating multiple gate pass records.
     */
    public function createExcel(Request $request)
    {
        // Get stock additions that have available stock (not fully issued)
        $stockAdditions = StockAddition::with(['product', 'mineVendor', 'stockIssued'])
            ->where('available_pieces', '>', 0)
            ->whereHas('product')
            ->whereHas('mineVendor')
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function ($stockAddition) {
                // Double-check that stock actually has available pieces
                return $stockAddition->hasAvailableStock();
            });

        // Get active machines and operators for dropdowns
        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        return view('stock-management.gate-pass.create-excel', compact('stockAdditions', 'machines', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_addition_id' => 'required|exists:stock_additions,id',
            'items.*.quantity_issued' => 'required|integer|min:1',
            'items.*.particulars' => 'nullable|string|max:1000',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_number' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Validate stock availability for all items
        foreach ($request->items as $index => $item) {
            $stockAddition = StockAddition::findOrFail($item['stock_addition_id']);
            if ($item['quantity_issued'] > $stockAddition->available_pieces) {
            return redirect()->back()
                ->withInput()
                    ->with('error', "Item " . ($index + 1) . ": Requested quantity ({$item['quantity_issued']}) exceeds available stock ({$stockAddition->available_pieces} pieces).");
            }
        }

        // Create gate pass first
        $gatePass = GatePass::create([
            'stock_issued_id' => null, // We'll handle this differently now
            'destination' => $request->destination,
            'vehicle_number' => $request->vehicle_number,
            'driver_name' => $request->driver_name,
            'client_name' => $request->client_name,
            'client_number' => $request->client_number,
            'status' => $request->status,
            'notes' => $request->notes,
            'date' => $request->date,
        ]);

        // Create gate pass items
        foreach ($request->items as $item) {
            $stockAddition = StockAddition::findOrFail($item['stock_addition_id']);

            // Calculate sqft and weight based on stock addition
            $sqftIssued = null;
            $weightIssued = null;
            
            if ($stockAddition->condition_status && in_array(strtolower($stockAddition->condition_status), ['block', 'monuments'])) {
                // For block/monuments, calculate weight
                if ($stockAddition->weight) {
                    $weightIssued = $stockAddition->weight * $item['quantity_issued'];
                }
            } else {
                // For other conditions, calculate sqft
                if ($stockAddition->total_sqft && $stockAddition->total_pieces > 0) {
                    $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                    $sqftIssued = $sqftPerPiece * $item['quantity_issued'];
                }
            }

            // Create gate pass item
            GatePassItem::create([
                'gate_pass_id' => $gatePass->id,
                'stock_addition_id' => $stockAddition->id,
                'quantity_issued' => $item['quantity_issued'],
                'sqft_issued' => $sqftIssued,
                'weight_issued' => $weightIssued,
                'stone' => $stockAddition->stone,
                'particulars' => $item['particulars'] ?? $stockAddition->stone,
            ]);

            // Update stock addition available quantities directly using withoutEvents to bypass observer
            StockAddition::withoutEvents(function() use ($stockAddition, $item, $sqftIssued, $weightIssued) {
                $stockAddition->available_pieces -= $item['quantity_issued'];
                if ($sqftIssued) {
                    $stockAddition->available_sqft -= $sqftIssued;
                }
                if ($weightIssued && isset($stockAddition->available_weight)) {
                    $stockAddition->available_weight -= $weightIssued;
                }
                $stockAddition->save();
            });

            // Log stock activity
            StockLog::logActivity(
                'dispatched',
                "Gate pass created - {$item['quantity_issued']} pieces dispatched to {$request->destination}",
                $stockAddition->id,
                null, // No stock_issued_id - gate pass doesn't use stock_issued table
                $gatePass->id,
                null,
                ['available_pieces' => $stockAddition->available_pieces + $item['quantity_issued'], 'available_sqft' => $stockAddition->available_sqft + ($sqftIssued ?? 0), 'available_weight' => isset($stockAddition->available_weight) ? $stockAddition->available_weight + ($weightIssued ?? 0) : null],
                ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft, 'available_weight' => $stockAddition->available_weight ?? null],
                -$item['quantity_issued'],
                (float)-($sqftIssued ?? 0),
                (float)-($weightIssued ?? 0)
            );
        }

        // Generate accounting journal entry
        $this->generateAccountingEntry($gatePass);

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass created successfully with ' . count($request->items) . ' items.');
    }

    /**
     * Store multiple gate pass records from Excel-style form.
     */
    public function storeMultiple(Request $request)
    {
        // Validate common fields
        $request->validate([
            'date' => 'required|date',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_number' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.stock_addition_id' => 'required|exists:stock_additions,id',
            'items.*.quantity_issued' => 'required|integer|min:1',
            'items.*.particulars' => 'nullable|string|max:1000',
        ]);

        $createdCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            // Create a single gate pass for all items
            $gatePass = GatePass::create([
                'date' => $request->date,
                'destination' => $request->destination,
                'vehicle_number' => $request->vehicle_number,
                'driver_name' => $request->driver_name,
                'client_name' => $request->client_name,
                'client_number' => $request->client_number,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $index => $itemData) {
                // Skip empty rows
                if (empty($itemData['stock_addition_id']) || empty($itemData['quantity_issued'])) {
                    continue;
                }

                $stockAddition = StockAddition::findOrFail($itemData['stock_addition_id']);
                $quantityToIssue = intval($itemData['quantity_issued']);

                // Check stock availability
                if ($quantityToIssue > $stockAddition->available_pieces) {
                    $errors[] = "Row " . ($index + 1) . ": Requested quantity ({$quantityToIssue}) exceeds available pieces ({$stockAddition->available_pieces}) for stock ID {$stockAddition->id}.";
                    continue;
                }

                // Calculate sqft and weight based on stock addition
                $sqftIssued = null;
                $weightIssued = null;
                
                if ($stockAddition->condition_status && in_array(strtolower($stockAddition->condition_status), ['block', 'monuments'])) {
                    // For block/monuments, calculate weight
                    if ($stockAddition->weight) {
                        $weightIssued = ($stockAddition->weight / $stockAddition->total_pieces) * $quantityToIssue;
                    }
                } else {
                    // For other conditions, calculate sqft
                    if ($stockAddition->total_sqft && $stockAddition->total_pieces > 0) {
                        $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                        $sqftIssued = $sqftPerPiece * $quantityToIssue;
                    }
                }

                // Create gate pass item for the single gate pass
                $gatePassItem = GatePassItem::create([
                    'gate_pass_id' => $gatePass->id,
                    'stock_addition_id' => $itemData['stock_addition_id'],
                    'quantity_issued' => $quantityToIssue,
                    'sqft_issued' => $sqftIssued,
                    'weight_issued' => $weightIssued,
                    'stone' => $stockAddition->stone,
                    'particulars' => $itemData['particulars'] ?? $stockAddition->stone,
                ]);

                // Update stock quantities directly using withoutEvents to bypass observer
                StockAddition::withoutEvents(function() use ($stockAddition, $quantityToIssue, $sqftIssued, $weightIssued) {
                    $stockAddition->available_pieces -= $quantityToIssue;
                    if ($sqftIssued) {
                        $stockAddition->available_sqft -= $sqftIssued;
                    }
                    if ($weightIssued && isset($stockAddition->available_weight)) {
                        $stockAddition->available_weight -= $weightIssued;
                    }
                    $stockAddition->save();
                });

                // Create stock log entry
                StockLog::logActivity(
                    'dispatched',
                    "Gate pass created - {$quantityToIssue} pieces dispatched to {$request->destination}",
                    $stockAddition->id,
                    null, // No stock_issued_id - gate pass doesn't use stock_issued table
                    $gatePass->id,
                    null,
                    ['available_pieces' => $stockAddition->available_pieces + $quantityToIssue, 'available_sqft' => $stockAddition->available_sqft + ($sqftIssued ?? 0), 'available_weight' => isset($stockAddition->available_weight) ? $stockAddition->available_weight + ($weightIssued ?? 0) : null],
                    ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft, 'available_weight' => $stockAddition->available_weight ?? null],
                    -$quantityToIssue,
                    (float)-($sqftIssued ?? 0),
                    (float)-($weightIssued ?? 0)
                );

                $createdCount++;
            }

            if (!empty($errors)) {
                \DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Some rows had validation errors: ' . implode(' ', $errors));
            }

            \DB::commit();

            // Generate accounting journal entries for the single gate pass
            if ($createdCount > 0) {
                $this->generateAccountingEntry($gatePass);
            }

            return redirect()->route('stock-management.gate-pass.index')
                ->with('success', "Successfully created gate pass with {$createdCount} item(s).");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Multiple gate pass creation error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create gate pass records. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GatePass $gatePass)
    {
        $gatePass->load(['items.stockAddition.product', 'items.stockAddition.mineVendor', 'stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);

        return view('stock-management.gate-pass.show', compact('gatePass'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GatePass $gatePass)
    {
        // Load the gate pass with its items
        $gatePass->load(['items.stockAddition.product', 'items.stockAddition.mineVendor']);
        
        // Get IDs of stock additions already used in this gate pass
        $usedStockIds = $gatePass->items->pluck('stock_addition_id')->toArray();
        
        $stockAdditions = StockAddition::with(['product', 'mineVendor', 'stockIssued'])
            ->where(function($query) use ($usedStockIds) {
                if (!empty($usedStockIds)) {
                    // Show either available stock OR stock already in this gate pass
                    $query->where(function($q) use ($usedStockIds) {
                        // Available stock that's not already in gate pass
                        $q->where(function($subQ) {
                            // Block and Monuments condition - check available_pieces OR available_weight > 0
                            $subQ->where(function($blockQ) {
                                $blockQ->whereRaw("LOWER(condition_status) IN ('block', 'monuments')")
                                       ->where(function($bQ) {
                                           $bQ->where('available_pieces', '>', 0)
                                              ->orWhere(function($wQ) {
                                                  $wQ->whereNotNull('available_weight')
                                                     ->where('available_weight', '>', 0);
                                              });
                                       });
                            })
                            // Other conditions - check available_pieces OR available_sqft > 0
                            ->orWhere(function($otherQ) {
                                $otherQ->where(function($notBlockQ) {
                                    $notBlockQ->whereRaw("LOWER(condition_status) NOT IN ('block', 'monuments')")
                                              ->orWhereNull('condition_status');
                                })
                                ->where(function($oQ) {
                                    $oQ->where('available_pieces', '>', 0)
                                       ->orWhere(function($sqftQ) {
                                           $sqftQ->whereNotNull('available_sqft')
                                                 ->where('available_sqft', '>', 0);
                                       });
                                });
                            });
                        })
                        ->whereNotIn('id', $usedStockIds); // Exclude stock already in gate pass
                    })->orWhereIn('id', $usedStockIds); // OR stock already in this gate pass
                } else {
                    // If no used stock, show available stock only
                    $query->where(function($q) {
                        $q->where(function($blockQ) {
                            // Block and Monuments - available_pieces > 0 OR available_weight > 0
                            $blockQ->whereRaw("LOWER(condition_status) IN ('block', 'monuments')")
                                   ->where(function($bQ) {
                                       $bQ->where('available_pieces', '>', 0)
                                          ->orWhere(function($wQ) {
                                              $wQ->whereNotNull('available_weight')
                                                 ->where('available_weight', '>', 0);
                                          });
                                   });
                        })->orWhere(function($otherQ) {
                            // Other conditions - available_pieces > 0 OR available_sqft > 0
                            $otherQ->where(function($notBlockQ) {
                                $notBlockQ->whereRaw("LOWER(condition_status) NOT IN ('block', 'monuments')")
                                          ->orWhereNull('condition_status');
                            })
                            ->where(function($oQ) {
                                $oQ->where('available_pieces', '>', 0)
                                   ->orWhere(function($sqftQ) {
                                       $sqftQ->whereNotNull('available_sqft')
                                             ->where('available_sqft', '>', 0);
                                   });
                            });
                        });
                    });
                }
            })
            ->whereHas('product')
            ->whereHas('mineVendor')
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function ($stockAddition) use ($usedStockIds) {
                // If already in gate pass, always show it
                if (in_array($stockAddition->id, $usedStockIds)) {
                    return true;
                }
                
                // For Block and Monuments: check available_pieces OR available_weight
                $conditionStatus = strtolower($stockAddition->condition_status ?? '');
                if (in_array($conditionStatus, ['block', 'monuments'])) {
                    // Must have at least one available: pieces OR weight (handle NULL)
                    $hasPieces = ($stockAddition->available_pieces ?? 0) > 0;
                    $hasWeight = ($stockAddition->available_weight ?? 0) > 0;
                    return $hasPieces || $hasWeight;
                } else {
                    // For other conditions: check available_pieces OR available_sqft (handle NULL)
                    $hasPieces = ($stockAddition->available_pieces ?? 0) > 0;
                    $hasSqft = ($stockAddition->available_sqft ?? 0) > 0;
                    return $hasPieces || $hasSqft;
                }
            });

        // Debug logging
        \Log::info('Gate Pass Edit - Stock Additions Count: ' . $stockAdditions->count());
        \Log::info('Gate Pass Edit - Used Stock IDs: ', $usedStockIds);
        \Log::info('Gate Pass Edit - Gate Pass Items Count: ' . $gatePass->items->count());

        return view('stock-management.gate-pass.edit', compact('gatePass', 'stockAdditions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GatePass $gatePass)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_addition_id' => 'required|exists:stock_additions,id',
            'items.*.quantity_issued' => 'required|integer|min:1',
            'items.*.particulars' => 'nullable|string|max:1000',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            'client_number' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Validate stock availability for all items
        foreach ($request->items as $index => $item) {
            $stockAddition = StockAddition::findOrFail($item['stock_addition_id']);

            // Check if this item already exists in the gate pass
            $existingItem = $gatePass->items()->where('stock_addition_id', $item['stock_addition_id'])->first();
            $currentIssued = $existingItem ? $existingItem->quantity_issued : 0;
            $availableForIncrease = $stockAddition->available_pieces + $currentIssued;

            if ($item['quantity_issued'] > $availableForIncrease) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Item " . ($index + 1) . ": Requested quantity ({$item['quantity_issued']}) exceeds available stock. Available: {$stockAddition->available_pieces} pieces + Current issued: {$currentIssued} pieces = {$availableForIncrease} pieces total.");
            }
        }

        // Get existing items for comparison
        $existingItems = $gatePass->items()->with('stockAddition')->get();
        $existingItemsMap = $existingItems->keyBy('stock_addition_id');

        $totalQuantity = 0;
        $totalSqft = 0;
        $processedStockAdditionIds = [];

        // Process each item in the request
        foreach ($request->items as $item) {
            $stockAddition = StockAddition::findOrFail($item['stock_addition_id']);
            $processedStockAdditionIds[] = $stockAddition->id;

            $existingItem = $existingItemsMap->get($stockAddition->id);

            if ($existingItem) {
                // Check if quantity or particulars changed
                $quantityChanged = $existingItem->quantity_issued != $item['quantity_issued'];
                $particularsChanged = ($existingItem->particulars ?? $stockAddition->stone) != ($item['particulars'] ?? $stockAddition->stone);
                
                if ($quantityChanged || $particularsChanged) {
                    // Update the gate pass item
                    $updateData = [
                        'particulars' => $item['particulars'] ?? $stockAddition->stone,
                    ];
                    
                    if ($quantityChanged) {
                        $quantityDifference = $item['quantity_issued'] - $existingItem->quantity_issued;
                        $newSqftIssued = ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'];
                        $newWeightIssued = ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'];
                        $sqftDifference = $newSqftIssued - ($existingItem->sqft_issued ?? 0);
                        $weightDifference = $newWeightIssued - ($existingItem->weight_issued ?? 0);
                        
                        $updateData = array_merge($updateData, [
                            'quantity_issued' => $item['quantity_issued'],
                            'sqft_issued' => $newSqftIssued,
                            'weight_issued' => $newWeightIssued,
                        ]);
                    }
                    
                    $existingItem->update($updateData);

                    // Adjust stock quantities if quantity changed
                    if ($quantityChanged) {
                        // Adjust stock quantities using withoutEvents to bypass observer
                        StockAddition::withoutEvents(function() use ($stockAddition, $quantityDifference, $sqftDifference, $weightDifference) {
                            $stockAddition->available_pieces -= $quantityDifference;
                            if ($sqftDifference != 0) {
                                $stockAddition->available_sqft -= $sqftDifference;
                            }
                            if ($weightDifference != 0 && isset($stockAddition->available_weight)) {
                                $stockAddition->available_weight -= $weightDifference;
                            }
                            $stockAddition->save();
                        });

                        // Log the update
                        StockLog::logActivity(
                            'updated',
                            "Gate pass item updated - {$quantityDifference} pieces change",
                            $stockAddition->id,
                            null,
                            $gatePass->id,
                            null,
                            ['available_pieces' => $stockAddition->available_pieces + $quantityDifference, 'available_sqft' => $stockAddition->available_sqft + $sqftDifference],
                            ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                            -$quantityDifference,
                            (float)-$sqftDifference,
                            (float)0
                        );
                    } else {
                        // Log particulars update
                        StockLog::logActivity(
                            'updated',
                            "Gate pass item particulars updated",
                            $stockAddition->id,
                            null,
                            $gatePass->id,
                            null,
                            null,
                            null,
                            0,
                            0.0,
                            0.0
                        );
                    }
                }
            } else {
                // New item - create it
                $sqftIssued = ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'];
                $weightIssued = ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'];

                // Create gate pass item
                GatePassItem::create([
                    'gate_pass_id' => $gatePass->id,
                    'stock_addition_id' => $stockAddition->id,
                    'quantity_issued' => $item['quantity_issued'],
                    'sqft_issued' => $sqftIssued,
                    'weight_issued' => $weightIssued,
                    'stone' => $stockAddition->stone,
                    'particulars' => $item['particulars'] ?? $stockAddition->stone,
                ]);

                // Adjust stock quantities using withoutEvents to bypass observer
                StockAddition::withoutEvents(function() use ($stockAddition, $item, $sqftIssued, $weightIssued) {
                    $stockAddition->available_pieces -= $item['quantity_issued'];
                    if ($sqftIssued) {
                        $stockAddition->available_sqft -= $sqftIssued;
                    }
                    if ($weightIssued && isset($stockAddition->available_weight)) {
                        $stockAddition->available_weight -= $weightIssued;
                    }
                    $stockAddition->save();
                });

                // Log the addition
                StockLog::logActivity(
                    'dispatched',
                    "Gate pass item added - {$item['quantity_issued']} pieces dispatched",
                    $stockAddition->id,
                    null,
                    $gatePass->id,
                    null,
                    ['available_pieces' => $stockAddition->available_pieces + $item['quantity_issued'], 'available_sqft' => $stockAddition->available_sqft + $sqftIssued],
                    ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                    -$item['quantity_issued'],
                    (float)-$sqftIssued,
                    (float)-($weightIssued ?? 0)
                );
            }

            // No need to calculate totals since they're now calculated from items
        }

        // Remove items that are no longer in the request
        $itemsToRemove = $existingItems->whereNotIn('stock_addition_id', $processedStockAdditionIds);
        foreach ($itemsToRemove as $itemToRemove) {
            // Restore stock using withoutEvents to bypass observer
            $stockAddition = $itemToRemove->stockAddition;
            if ($stockAddition) {
                StockAddition::withoutEvents(function() use ($stockAddition, $itemToRemove) {
                    $stockAddition->available_pieces += $itemToRemove->quantity_issued;
                    if ($itemToRemove->sqft_issued) {
                        $stockAddition->available_sqft += $itemToRemove->sqft_issued;
                    }
                    if ($itemToRemove->weight_issued && isset($stockAddition->available_weight)) {
                        $stockAddition->available_weight += $itemToRemove->weight_issued;
                    }
                    $stockAddition->save();
                });

                // Log the removal
                StockLog::logActivity(
                    'restored',
                    "Gate pass item removed - {$itemToRemove->quantity_issued} pieces restored",
                    $stockAddition->id,
                    null,
                    $gatePass->id,
                    null,
                    ['available_pieces' => $stockAddition->available_pieces - $itemToRemove->quantity_issued, 'available_sqft' => $stockAddition->available_sqft - ($itemToRemove->sqft_issued ?? 0)],
                    ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                    $itemToRemove->quantity_issued,
                    (float)($itemToRemove->sqft_issued ?? 0),
                    (float)($itemToRemove->weight_issued ?? 0)
                );
            }

            // Delete the gate pass item
            $itemToRemove->delete();
        }

        // Update gate pass with other fields (totals are now calculated from items)
        $gatePass->update([
            'destination' => $request->destination,
            'vehicle_number' => $request->vehicle_number,
            'driver_name' => $request->driver_name,
            'client_name' => $request->client_name,
            'client_number' => $request->client_number,
            'status' => $request->status,
            'notes' => $request->notes,
            'date' => $request->date,
        ]);

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass updated successfully with ' . count($request->items) . ' items.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GatePass $gatePass)
    {
        // Delete the gate pass - stock restoration is now handled in the model's deleting event
        $gatePass->delete();

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass deleted successfully and stock has been restored.');
    }

    /**
     * Print gate pass.
     */
    public function print(GatePass $gatePass)
    {
        $gatePass->load([
            'items.stockAddition.product',
            'items.stockAddition.mineVendor',
            'stockIssued.stockAddition.product',
            'stockIssued.stockAddition.mineVendor'
        ]);

        return view('stock-management.gate-pass.print', compact('gatePass'));
    }

    /**
     * Get remaining quantity for a stock issued.
     */
    public function getRemainingQuantity(Request $request)
    {
        $stockIssuedId = $request->get('stock_issued_id');

        $stockIssued = StockIssued::findOrFail($stockIssuedId);
        $totalGatePasses = $stockIssued->gatePasses()->sum('quantity_issued');
        $remainingQuantity = $stockIssued->quantity_issued - $totalGatePasses;

        return response()->json([
            'remaining_quantity' => $remainingQuantity,
            'total_issued' => $stockIssued->quantity_issued,
            'total_gate_passes' => $totalGatePasses,
        ]);
    }

    /**
     * Generate accounting journal entry for gate pass.
     */
    private function generateAccountingEntry(GatePass $gatePass)
    {
        try {
            // Get accounts
            $cogsAccount = ChartOfAccount::where('account_code', '5110')->first(); // Cost of Goods Sold
            $finishedGoodsAccount = ChartOfAccount::where('account_code', '1160')->first(); // Finished Goods

            if (! $cogsAccount || ! $finishedGoodsAccount) {
                \Log::warning('Required accounts not found for gate pass accounting entry');

                return;
            }

            // Calculate cost (you might want to add cost fields)
            $costPerSqft = 100; // This should come from your data
            $totalCost = $gatePass->sqft_issued * $costPerSqft; // Use calculated sqft from items

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_date' => $gatePass->date,
                'description' => "Gate pass: {$gatePass->stockIssued->stockAddition->product->name} to {$gatePass->destination}",
                'entry_type' => 'AUTO_GATE_PASS',
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'notes' => "Auto-generated for gate pass #{$gatePass->id}",
            ]);

            // Create transactions
            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $cogsAccount->id,
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'description' => "Cost of goods sold: {$gatePass->stockIssued->stockAddition->product->name}",
                'reference_type' => 'gate_pass',
                'reference_id' => $gatePass->id,
            ]);

            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $finishedGoodsAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'description' => "Finished goods decrease: {$gatePass->stockIssued->stockAddition->product->name}",
                'reference_type' => 'gate_pass',
                'reference_id' => $gatePass->id,
            ]);

            \Log::info("Accounting entry created for gate pass #{$gatePass->id}");

        } catch (\Exception $e) {
            \Log::error("Failed to create accounting entry for gate pass #{$gatePass->id}: ".$e->getMessage());
        }
    }

    /**
     * Generate accounting journal entries for multiple gate passes.
     */
    private function generateAccountingEntryForMultiple($items)
    {
        try {
            // Get accounts
            $cogsAccount = ChartOfAccount::where('account_code', '5110')->first(); // Cost of Goods Sold
            $finishedGoodsAccount = ChartOfAccount::where('account_code', '1160')->first(); // Finished Goods

            if (! $cogsAccount || ! $finishedGoodsAccount) {
                \Log::warning('Required accounts not found for multiple gate pass accounting entry');
                return;
            }

            $totalCost = 0;
            $costPerSqft = 100; // This should come from your data

            // Calculate total cost from all items
            foreach ($items as $itemData) {
                if (empty($itemData['stock_addition_id']) || empty($itemData['quantity_issued'])) {
                    continue;
                }

                $stockAddition = StockAddition::find($itemData['stock_addition_id']);
                if ($stockAddition && $stockAddition->total_sqft && $stockAddition->total_pieces > 0) {
                    $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                    $sqftIssued = $sqftPerPiece * intval($itemData['quantity_issued']);
                    $totalCost += $sqftIssued * $costPerSqft;
                }
            }

            if ($totalCost > 0) {
                // Create journal entry
                $journalEntry = JournalEntry::create([
                    'entry_date' => now()->format('Y-m-d'),
                    'description' => "Multiple gate passes created",
                    'entry_type' => 'AUTO_GATE_PASS_MULTIPLE',
                    'total_debit' => $totalCost,
                    'total_credit' => $totalCost,
                    'status' => 'DRAFT',
                    'created_by' => auth()->id(),
                    'notes' => "Auto-generated for multiple gate passes",
                ]);

                // Create transactions
                AccountTransaction::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $cogsAccount->id,
                    'debit_amount' => $totalCost,
                    'credit_amount' => 0,
                    'description' => "Cost of goods sold: Multiple gate passes",
                    'reference_type' => 'gate_pass_multiple',
                    'reference_id' => null,
                ]);

                AccountTransaction::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $finishedGoodsAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $totalCost,
                    'description' => "Finished goods decrease: Multiple gate passes",
                    'reference_type' => 'gate_pass_multiple',
                    'reference_id' => null,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Multiple gate pass accounting entry generation failed: ' . $e->getMessage());
        }
    }
}

