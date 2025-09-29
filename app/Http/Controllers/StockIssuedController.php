<?php

namespace App\Http\Controllers;

use App\Models\StockIssued;
use App\Models\StockAddition;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use App\Models\Machine;
use App\Models\Operator;
use Illuminate\Http\Request;

class StockIssuedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
            ->whereDoesntHave('dailyProduction', function ($dailyQuery) {
                $dailyQuery->where('status', 'close');
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhere('machine_name', 'like', "%{$search}%")
                  ->orWhere('operator_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('stockAddition.product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('stockAddition.mineVendor', function ($vendorQuery) use ($search) {
                      $vendorQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('stockAddition', function ($stockQuery) use ($search) {
                      $stockQuery->where('stone', 'like', "%{$search}%")
                                ->orWhere('size_3d', 'like', "%{$search}%")
                                ->orWhere('length', 'like', "%{$search}%")
                                ->orWhere('height', 'like', "%{$search}%")
                                ->orWhere('condition_status', 'like', "%{$search}%");
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

        // Filter by condition status
        if ($request->filled('condition_status')) {
            $query->whereHas('stockAddition', function ($stockQuery) use ($request) {
                $stockQuery->where('condition_status', $request->get('condition_status'));
            });
        }

        // Filter by purpose
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->get('purpose'));
        }

        // Filter by machine name
        if ($request->filled('machine_name')) {
            $query->where('machine_name', 'like', "%{$request->get('machine_name')}%");
        }

        // Filter by operator name
        if ($request->filled('operator_name')) {
            $query->where('operator_name', 'like', "%{$request->get('operator_name')}%");
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
                $query->join('stock_additions', 'stock_issued.stock_addition_id', '=', 'stock_additions.id')
                      ->join('products', 'stock_additions.product_id', '=', 'products.id')
                      ->orderBy('products.name', $sortOrder)
                      ->select('stock_issued.*');
                break;
            case 'vendor':
                $query->join('stock_additions', 'stock_issued.stock_addition_id', '=', 'stock_additions.id')
                      ->join('mine_vendors', 'stock_additions.mine_vendor_id', '=', 'mine_vendors.id')
                      ->orderBy('mine_vendors.name', $sortOrder)
                      ->select('stock_issued.*');
                break;
            case 'quantity':
                $query->orderBy('quantity_issued', $sortOrder);
                break;
            case 'sqft':
                $query->orderBy('sqft_issued', $sortOrder);
                break;
            case 'purpose':
                $query->orderBy('purpose', $sortOrder);
                break;
            default:
                $query->orderBy('date', $sortOrder);
                break;
        }

        $stockIssued = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = \App\Models\Product::orderBy('name')->get();
        $vendors = \App\Models\MineVendor::orderBy('name')->get();
        $conditionStatuses = StockAddition::distinct()->pluck('condition_status')->filter()->sort()->values();
        $purposes = StockIssued::distinct()->pluck('purpose')->filter()->sort()->values();
        $machines = StockIssued::whereNotNull('machine_name')->distinct()->pluck('machine_name')->filter()->sort()->values();
        $operators = StockIssued::whereNotNull('operator_name')->distinct()->pluck('operator_name')->filter()->sort()->values();

        return view('stock-management.stock-issued.index', compact('stockIssued', 'products', 'vendors', 'conditionStatuses', 'purposes', 'machines', 'operators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $stockAdditionId = $request->get('stock_addition_id');
        $availableStock = null;

        if ($stockAdditionId) {
            $availableStock = StockAddition::with(['product', 'mineVendor'])
                ->where('id', $stockAdditionId)
                ->where('available_pieces', '>', 0)
                ->first();
        }

        $stockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->where('available_pieces', '>', 0)
            ->orderBy('date', 'asc')
            ->get();

        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        return view('stock-management.stock-issued.create', compact('availableStock', 'stockAdditions', 'machines', 'operators'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'quantity_issued' => 'required|integer|min:1',
            'sqft_issued' => 'required|numeric|min:0',
            'purpose' => 'required|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'operator_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'stone' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available
        if ($request->quantity_issued > $stockAddition->available_pieces) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available stock.');
        }

        // Check if requested sqft is available
        if ($request->sqft_issued > $stockAddition->available_sqft) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested square footage exceeds available stock.');
        }

        $stockIssued = StockIssued::create($request->all());

        // The model's boot method will automatically update available stock quantities

        return redirect()->route('stock-management.stock-issued.index')
            ->with('success', 'Stock issued successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockIssued $stockIssued)
    {
        $stockIssued->load(['stockAddition.product', 'stockAddition.mineVendor', 'gatePass', 'stockAddition.dailyProduction']);

        return view('stock-management.stock-issued.show', compact('stockIssued'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockIssued $stockIssued)
    {
        $availableStockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->where('available_pieces', '>', 0)
            ->orWhere('id', $stockIssued->stock_addition_id)
            ->orderBy('date', 'asc')
            ->get();

        $machines = Machine::active()->orderBy('name')->get();
        $operators = Operator::active()->orderBy('name')->get();

        return view('stock-management.stock-issued.edit', compact('stockIssued', 'availableStockAdditions', 'machines', 'operators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockIssued $stockIssued)
    {
        // Check if this stock issue is linked to a closed daily production
        $closedProduction = $stockIssued->dailyProduction()->where('status', 'close')->first();
        if ($closedProduction) {
            return redirect()->back()
                ->with('error', 'Cannot update stock issue that is linked to a closed daily production.');
        }

        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'quantity_issued' => 'required|integer|min:1',
            'purpose' => 'nullable|string|max:255',
            'machine_name' => 'nullable|string|max:255',
            'operator_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'stone' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available (considering current issuance)
        $currentIssued = $stockIssued->quantity_issued;
        $availableAfterRestore = $stockAddition->available_pieces + $currentIssued;

        if ($request->quantity_issued > $availableAfterRestore) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available stock.');
        }

        $stockIssued->update($request->all());

        return redirect()->route('stock-management.stock-issued.index')
            ->with('success', 'Stock issuance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockIssued $stockIssued)
    {
        // Check if this stock issue is linked to a closed daily production
        $closedProduction = $stockIssued->dailyProduction()->where('status', 'close')->first();
        if ($closedProduction) {
            return redirect()->route('stock-management.stock-issued.index')
                ->with('error', 'Cannot delete stock issue that is linked to a closed daily production.');
        }

        // Check if there are any gate passes
        if ($stockIssued->gatePass()->count() > 0) {
            return redirect()->route('stock-management.stock-issued.index')
                ->with('error', 'Cannot delete stock issuance with existing gate passes.');
        }

        // Delete the stock issued record - the model's boot method will handle restoring available quantities
        $stockIssued->delete();

        return redirect()->route('stock-management.stock-issued.index')
            ->with('success', 'Stock issuance deleted successfully.');
    }

    /**
     * Get available quantity for a stock addition.
     */
    public function getAvailableQuantity(Request $request)
    {
        $stockAdditionId = $request->get('stock_addition_id');

        $stockAddition = StockAddition::findOrFail($stockAdditionId);

        return response()->json([
            'available_pieces' => $stockAddition->available_pieces,
            'available_sqft' => $stockAddition->available_sqft,
            'total_pieces' => $stockAddition->total_pieces,
            'total_sqft' => $stockAddition->total_sqft,
            'sqft_per_piece' => $stockAddition->total_sqft / $stockAddition->total_pieces
        ]);
    }

    /**
     * Generate accounting journal entry for stock issued.
     */
    private function generateAccountingEntry(StockIssued $stockIssued)
    {
        try {
            // Get accounts
            $wipAccount = ChartOfAccount::where('account_code', '1150')->first(); // Work in Progress
            $inventoryAccount = ChartOfAccount::where('account_code', '1130')->first(); // Stone Inventory

            if (!$wipAccount || !$inventoryAccount) {
                \Log::warning('Required accounts not found for stock issued accounting entry');
                return;
            }

            // Calculate cost (you might want to add cost fields)
            $costPerSqft = 100; // This should come from your data
            $totalCost = $stockIssued->sqft_issued * $costPerSqft;

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'entry_date' => $stockIssued->date,
                'description' => "Stock issued for {$stockIssued->purpose}: {$stockIssued->stockAddition->product->name}",
                'entry_type' => 'AUTO_STOCK_ISSUE',
                'total_debit' => $totalCost,
                'total_credit' => $totalCost,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'notes' => "Auto-generated for stock issued #{$stockIssued->id}"
            ]);

            // Create transactions
            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $wipAccount->id,
                'debit_amount' => $totalCost,
                'credit_amount' => 0,
                'description' => "Work in progress: {$stockIssued->stockAddition->product->name}",
                'reference_type' => 'stock_issued',
                'reference_id' => $stockIssued->id
            ]);

            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $totalCost,
                'description' => "Inventory decrease: {$stockIssued->stockAddition->product->name}",
                'reference_type' => 'stock_issued',
                'reference_id' => $stockIssued->id
            ]);

            \Log::info("Accounting entry created for stock issued #{$stockIssued->id}");

        } catch (\Exception $e) {
            \Log::error("Failed to create accounting entry for stock issued #{$stockIssued->id}: " . $e->getMessage());
        }
    }
}
