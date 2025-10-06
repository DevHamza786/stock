<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use App\Models\GatePass;
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
        $query = GatePass::with(['stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor'])
            ->whereNotNull('stock_issued_id');
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('destination', 'like', "%{$search}%")
                    ->orWhere('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
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
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'quantity_issued' => 'required|integer|min:1',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available
        if ($request->quantity_issued > $stockAddition->available_pieces) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available stock.');
        }

        // Create stock issued record first
        $stockIssued = StockIssued::create([
            'stock_addition_id' => $stockAddition->id,
            'quantity_issued' => $request->quantity_issued,
            'sqft_issued' => ($stockAddition->total_sqft / $stockAddition->total_pieces) * $request->quantity_issued,
            'weight_issued' => ($stockAddition->weight / $stockAddition->total_pieces) * $request->quantity_issued,
            'purpose' => 'Gate Pass Dispatch',
            'notes' => 'Auto-created for gate pass dispatch',
            'stone' => $stockAddition->stone,
            'date' => $request->date,
        ]);

        // Create gate pass with stock_issued_id
        $gatePassData = $request->all();
        $gatePassData['stock_issued_id'] = $stockIssued->id;
        $gatePass = GatePass::create($gatePassData);

        // StockIssued model observer will automatically update available pieces
        // No manual update needed here

        // Generate accounting journal entry
        $this->generateAccountingEntry($gatePass);

        // Log stock activity (refresh to get updated values from observer)
        $stockAddition->refresh();
        StockLog::logActivity(
            'dispatched',
            "Gate pass created - {$request->quantity_issued} pieces dispatched to {$request->destination}",
            $stockAddition->id,
            $stockIssued->id,
            $gatePass->id,
            null,
            ['available_pieces' => $stockAddition->available_pieces + $request->quantity_issued, 'available_sqft' => $stockAddition->available_sqft + $stockIssued->sqft_issued],
            ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
            -$request->quantity_issued,
            -$stockIssued->sqft_issued
        );

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GatePass $gatePass)
    {
        $gatePass->load(['stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);

        return view('stock-management.gate-pass.show', compact('gatePass'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GatePass $gatePass)
    {
        $stockAdditions = StockAddition::with(['product', 'mineVendor', 'stockIssued'])
            ->where(function($query) use ($gatePass) {
                $query->where('available_pieces', '>', 0)
                      ->orWhere('id', $gatePass->stockIssued->stock_addition_id);
            })
            ->whereHas('product')
            ->whereHas('mineVendor')
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function ($stockAddition) use ($gatePass) {
                // Show stock with available pieces OR the currently selected stock
                return $stockAddition->hasAvailableStock() ||
                       $stockAddition->id == $gatePass->stockIssued->stock_addition_id;
            });

        return view('stock-management.gate-pass.edit', compact('gatePass', 'stockAdditions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GatePass $gatePass)
    {
        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'quantity_issued' => 'required|integer|min:1',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);
        $oldStockIssued = $gatePass->stockIssued;

        // If changing stock addition, check availability
        if ($oldStockIssued->stock_addition_id != $request->stock_addition_id) {
            // Check if requested quantity is available in new stock addition
            if ($request->quantity_issued > $stockAddition->available_pieces) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Requested quantity ({$request->quantity_issued}) exceeds available stock in selected stock addition. Available: {$stockAddition->available_pieces} pieces.");
            }
        } else {
            // Same stock addition, check if new quantity is within available + current issued
            $currentIssued = $oldStockIssued->quantity_issued;
            $availableForIncrease = $stockAddition->available_pieces + $currentIssued;

            if ($request->quantity_issued > $availableForIncrease) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Requested quantity ({$request->quantity_issued}) exceeds available stock. Available: {$stockAddition->available_pieces} pieces + Current issued: {$currentIssued} pieces = {$availableForIncrease} pieces total.");
            }
        }

        // Store old values for logging
        $oldQuantity = $oldStockIssued->quantity_issued;
        $oldSqft = $oldStockIssued->sqft_issued;
        $oldStockAdditionId = $oldStockIssued->stock_addition_id;

        // Update the existing StockIssued record instead of deleting and recreating
        $newSqft = ($stockAddition->total_sqft / $stockAddition->total_pieces) * $request->quantity_issued;
        $newWeight = ($stockAddition->weight / $stockAddition->total_pieces) * $request->quantity_issued;

        $oldStockIssued->update([
            'stock_addition_id' => $stockAddition->id,
            'quantity_issued' => $request->quantity_issued,
            'sqft_issued' => $newSqft,
            'weight_issued' => $newWeight,
            'stone' => $stockAddition->stone,
            'date' => $request->date,
        ]);

        // Update gate pass
        $gatePassData = $request->all();
        $gatePass->update($gatePassData);

        // Log the update activity
        StockLog::logActivity(
            'updated',
            "Gate pass updated - quantity changed from {$oldQuantity} to {$request->quantity_issued} pieces",
            $stockAddition->id,
            $oldStockIssued->id,
            $gatePass->id,
            null,
            [
                'stock_addition_id' => $oldStockAdditionId,
                'quantity_issued' => $oldQuantity,
                'sqft_issued' => $oldSqft
            ],
            [
                'stock_addition_id' => $stockAddition->id,
                'quantity_issued' => $request->quantity_issued,
                'sqft_issued' => $newSqft
            ],
            $request->quantity_issued - $oldQuantity,
            $newSqft - $oldSqft
        );

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass updated successfully.');
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
        $gatePass->load(['stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);

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
