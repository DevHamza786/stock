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
                $query->orderBy('quantity_issued', $sortOrder);
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
        $statuses = GatePass::distinct()->pluck('status')->filter()->sort()->values();
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_addition_id' => 'required|exists:stock_additions,id',
            'items.*.quantity_issued' => 'required|integer|min:1',
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
            'quantity_issued' => 0, // Will be calculated from items
            'sqft_issued' => 0, // Will be calculated from items
            'destination' => $request->destination,
            'vehicle_number' => $request->vehicle_number,
            'driver_name' => $request->driver_name,
            'client_name' => $request->client_name,
            'client_number' => $request->client_number,
            'status' => $request->status,
            'notes' => $request->notes,
            'date' => $request->date,
        ]);

        $totalQuantity = 0;
        $totalSqft = 0;

        // Create gate pass items and stock issued records
        foreach ($request->items as $item) {
            $stockAddition = StockAddition::findOrFail($item['stock_addition_id']);

            // Create stock issued record
        $stockIssued = StockIssued::create([
            'stock_addition_id' => $stockAddition->id,
                'quantity_issued' => $item['quantity_issued'],
                'sqft_issued' => ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'],
                'weight_issued' => ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'],
            'purpose' => 'Gate Pass Dispatch',
            'notes' => 'Auto-created for gate pass dispatch',
            'stone' => $stockAddition->stone,
            'date' => $request->date,
        ]);

            // Create gate pass item
            GatePassItem::create([
                'gate_pass_id' => $gatePass->id,
                'stock_addition_id' => $stockAddition->id,
                'quantity_issued' => $item['quantity_issued'],
                'sqft_issued' => $stockIssued->sqft_issued,
                'weight_issued' => $stockIssued->weight_issued,
                'stone' => $stockAddition->stone,
            ]);

            $totalQuantity += $item['quantity_issued'];
            $totalSqft += $stockIssued->sqft_issued;

            // Log stock activity
        $stockAddition->refresh();
        StockLog::logActivity(
            'dispatched',
                "Gate pass created - {$item['quantity_issued']} pieces dispatched to {$request->destination}",
            $stockAddition->id,
            $stockIssued->id,
            $gatePass->id,
            null,
                ['available_pieces' => $stockAddition->available_pieces + $item['quantity_issued'], 'available_sqft' => $stockAddition->available_sqft + $stockIssued->sqft_issued],
            ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                -$item['quantity_issued'],
            -$stockIssued->sqft_issued
        );
        }

        // Update gate pass with totals
        $gatePass->update([
            'quantity_issued' => $totalQuantity,
            'sqft_issued' => $totalSqft,
        ]);

        // Generate accounting journal entry
        $this->generateAccountingEntry($gatePass);

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass created successfully with ' . count($request->items) . ' items.');
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
                // Include stock with available pieces OR stock already used in this gate pass
                $query->where('available_pieces', '>', 0);
                if (!empty($usedStockIds)) {
                    $query->orWhereIn('id', $usedStockIds);
                }
            })
            ->whereHas('product')
            ->whereHas('mineVendor')
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function ($stockAddition) use ($usedStockIds) {
                // Show stock with available pieces OR the currently used stock in this gate pass
                return $stockAddition->hasAvailableStock() || in_array($stockAddition->id, $usedStockIds);
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
                // Item exists - check if quantity changed
                if ($existingItem->quantity_issued != $item['quantity_issued']) {
                    // Quantity changed - update the item
                    $quantityDifference = $item['quantity_issued'] - $existingItem->quantity_issued;
                    $sqftDifference = (($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued']) - $existingItem->sqft_issued;

                    // Update the gate pass item
                    $existingItem->update([
                        'quantity_issued' => $item['quantity_issued'],
                        'sqft_issued' => ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'],
                        'weight_issued' => ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'],
                    ]);

                    // Update the corresponding stock issued record
                    $stockIssued = StockIssued::where('stock_addition_id', $stockAddition->id)
                        ->where('purpose', 'Gate Pass Dispatch')
                        ->where('notes', 'like', '%Auto-created for gate pass dispatch%')
                        ->first();

                    if ($stockIssued) {
                        $stockIssued->update([
                            'quantity_issued' => $item['quantity_issued'],
                            'sqft_issued' => ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'],
                            'weight_issued' => ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'],
                        ]);
                    }

                    // Adjust stock quantities
                    $stockAddition->available_pieces -= $quantityDifference;
                    $stockAddition->available_sqft -= $sqftDifference;
                    $stockAddition->save();

                    // Log the update
                    StockLog::logActivity(
                        'updated',
                        "Gate pass item updated - {$quantityDifference} pieces change",
                        $stockAddition->id,
                        $stockIssued->id,
                        $gatePass->id,
                        null,
                        ['available_pieces' => $stockAddition->available_pieces + $quantityDifference, 'available_sqft' => $stockAddition->available_sqft + $sqftDifference],
                        ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                        -$quantityDifference,
                        -$sqftDifference
                    );
                }
            } else {
                // New item - create it
                $stockIssued = StockIssued::create([
            'stock_addition_id' => $stockAddition->id,
                    'quantity_issued' => $item['quantity_issued'],
                    'sqft_issued' => ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'],
                    'weight_issued' => ($stockAddition->weight / $stockAddition->total_pieces) * $item['quantity_issued'],
                    'purpose' => 'Gate Pass Dispatch',
                    'notes' => 'Auto-created for gate pass dispatch',
            'stone' => $stockAddition->stone,
            'date' => $request->date,
        ]);

                // Create gate pass item
                GatePassItem::create([
                    'gate_pass_id' => $gatePass->id,
                    'stock_addition_id' => $stockAddition->id,
                    'quantity_issued' => $item['quantity_issued'],
                    'sqft_issued' => $stockIssued->sqft_issued,
                    'weight_issued' => $stockIssued->weight_issued,
                    'stone' => $stockAddition->stone,
                ]);

                // Adjust stock quantities
                $stockAddition->available_pieces -= $item['quantity_issued'];
                $stockAddition->available_sqft -= $stockIssued->sqft_issued;
                $stockAddition->save();

                // Log the addition
                StockLog::logActivity(
                    'dispatched',
                    "Gate pass item added - {$item['quantity_issued']} pieces dispatched",
                    $stockAddition->id,
                    $stockIssued->id,
                    $gatePass->id,
                    null,
                    ['available_pieces' => $stockAddition->available_pieces + $item['quantity_issued'], 'available_sqft' => $stockAddition->available_sqft + $stockIssued->sqft_issued],
                    ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                    -$item['quantity_issued'],
                    -$stockIssued->sqft_issued
                );
            }

            $totalQuantity += $item['quantity_issued'];
            $totalSqft += ($stockAddition->total_sqft / $stockAddition->total_pieces) * $item['quantity_issued'];
        }

        // Remove items that are no longer in the request
        $itemsToRemove = $existingItems->whereNotIn('stock_addition_id', $processedStockAdditionIds);
        foreach ($itemsToRemove as $itemToRemove) {
            // Find and delete the corresponding stock issued record
            $stockIssued = StockIssued::where('stock_addition_id', $itemToRemove->stock_addition_id)
                ->where('purpose', 'Gate Pass Dispatch')
                ->where('notes', 'like', '%Auto-created for gate pass dispatch%')
                ->first();

            if ($stockIssued) {
                // Restore stock
                $stockAddition = $stockIssued->stockAddition;
                if ($stockAddition) {
                    $stockAddition->available_pieces += $stockIssued->quantity_issued;
                    $stockAddition->available_sqft += $stockIssued->sqft_issued;
                    $stockAddition->save();

                    // Log the removal
        StockLog::logActivity(
                        'restored',
                        "Gate pass item removed - {$stockIssued->quantity_issued} pieces restored",
            $stockAddition->id,
                        $stockIssued->id,
            $gatePass->id,
            null,
                        ['available_pieces' => $stockAddition->available_pieces - $stockIssued->quantity_issued, 'available_sqft' => $stockAddition->available_sqft - $stockIssued->sqft_issued],
                        ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                        $stockIssued->quantity_issued,
                        $stockIssued->sqft_issued
                    );
                }

                // Delete the stock issued record
                $stockIssued->deleteQuietly();
            }

            // Delete the gate pass item
            $itemToRemove->delete();
        }

        // Update gate pass with new totals and other fields
        $gatePass->update([
            'quantity_issued' => $totalQuantity,
            'sqft_issued' => $totalSqft,
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
            $totalCost = $gatePass->quantity_issued * $costPerSqft; // Assuming quantity_issued is in sqft

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
}
