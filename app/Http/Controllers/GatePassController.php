<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use App\Models\GatePass;
use App\Models\JournalEntry;
use App\Models\StockIssued;
use Illuminate\Http\Request;

class GatePassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GatePass::with(['stockIssued.stockAddition.product', 'stockIssued.stockAddition.mineVendor']);

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
        $stockIssuedId = $request->get('stock_issued_id');
        $selectedStockIssued = null;

        if ($stockIssuedId) {
            $selectedStockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
                ->findOrFail($stockIssuedId);
        }

        // Get stock issued records that have available stock in stock_additions table
        $stockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
            ->whereHas('stockAddition', function ($query) {
                $query->where('available_pieces', '>', 0);
            })
            ->where('quantity_issued', '>', 0)
            ->orderBy('date', 'desc')
            ->get();

        return view('stock-management.gate-pass.create', compact('selectedStockIssued', 'stockIssued'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_issued_id' => 'required|exists:stock_issued,id',
            'quantity_issued' => 'required|integer|min:1',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockIssued = StockIssued::findOrFail($request->stock_issued_id);

        // Check if requested quantity is available
        if ($request->quantity_issued > $stockIssued->quantity_issued) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available issued stock.');
        }

        $gatePass = GatePass::create($request->all());

        // Generate accounting journal entry
        $this->generateAccountingEntry($gatePass);

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
        $stockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
            ->where('quantity_issued', '>', 0)
            ->orWhere('id', $gatePass->stock_issued_id)
            ->orderBy('date', 'asc')
            ->get();

        return view('stock-management.gate-pass.edit', compact('gatePass', 'stockIssued'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GatePass $gatePass)
    {
        $request->validate([
            'stock_issued_id' => 'required|exists:stock_issued,id',
            'quantity_issued' => 'required|integer|min:1',
            'destination' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockIssued = StockIssued::findOrFail($request->stock_issued_id);

        // Check if requested quantity is available (considering current gate pass)
        $currentQuantity = $gatePass->quantity_issued;
        $availableAfterRestore = $stockIssued->quantity_issued + $currentQuantity;

        if ($request->quantity_issued > $availableAfterRestore) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available issued stock.');
        }

        $gatePass->update($request->all());

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GatePass $gatePass)
    {
        $gatePass->delete();

        return redirect()->route('stock-management.gate-pass.index')
            ->with('success', 'Gate pass deleted successfully.');
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
