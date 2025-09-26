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
            $query->where('condition_status', $request->get('condition_status'));
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
                $query->orderBy('condition_status', $sortOrder);
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
        $conditionStatuses = DailyProduction::distinct()->pluck('condition_status')->filter()->sort()->values();

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

        // Get stock issued records for production (where purpose is 'Production')
        $availableStockIssued = StockIssued::with(['stockAddition.product', 'stockAddition.mineVendor'])
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_issued_id' => 'required|exists:stock_issued,id',
            'machine_name' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.size' => 'nullable|string|max:255',
            'items.*.diameter' => 'nullable|string|max:255',
            'items.*.condition_status' => 'required|string|max:255',
            'items.*.special_status' => 'nullable|string|max:255',
            'items.*.total_pieces' => 'required|integer|min:1',
            'items.*.total_sqft' => 'required|numeric|min:0',
            'items.*.narration' => 'nullable|string',
        ]);

        $stockIssued = StockIssued::with('stockAddition')->findOrFail($request->stock_issued_id);

        // Calculate total pieces from all items
        $totalPieces = collect($request->items)->sum('total_pieces');

        // Check if requested quantity is available
        if ($totalPieces > $stockIssued->quantity_issued) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total production quantity exceeds issued stock.');
        }

        // Create daily production record
        $dailyProduction = DailyProduction::create([
            'stock_addition_id' => $stockIssued->stock_addition_id,
            'stock_issued_id' => $request->stock_issued_id,
            'machine_name' => $request->machine_name,
            'operator_name' => $request->operator_name,
            'notes' => $request->notes,
            'date' => $request->date,
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

            // Check if similar product already exists in processed items
            if (isset($processedItems[$productKey])) {
                // Merge quantities
                $processedItems[$productKey]['total_pieces'] += $itemData['total_pieces'];
                $processedItems[$productKey]['total_sqft'] += $itemData['total_sqft'];

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
            $dailyProduction->items()->create($itemData);
        }

        // Generate accounting journal entry
        $this->generateAccountingEntry($dailyProduction);

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production recorded successfully with ' . count($processedItems) . ' product(s).');
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyProduction $dailyProduction)
    {
        $dailyProduction->load(['stockAddition.product', 'stockAddition.mineVendor', 'items', 'machine', 'operator']);

        return view('stock-management.daily-production.show', compact('dailyProduction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyProduction $dailyProduction)
    {
        $availableStockAdditions = StockAddition::with(['product', 'mineVendor'])
            ->where('available_pieces', '>', 0)
            ->orWhere('id', $dailyProduction->stock_addition_id)
            ->orderBy('date', 'asc')
            ->get();

        // Get condition statuses from database
        $conditionStatuses = ConditionStatus::active()->orderBy('name')->get();

        return view('stock-management.daily-production.edit', compact('dailyProduction', 'availableStockAdditions', 'conditionStatuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyProduction $dailyProduction)
    {
        $request->validate([
            'stock_addition_id' => 'required|exists:stock_additions,id',
            'machine_name' => 'required|string|max:255',
            'product' => 'required|string|max:255',
            'operator_name' => 'required|string|max:255',
            'total_pieces' => 'required|integer|min:1',
            'total_sqft' => 'required|numeric|min:0',
            'condition_status' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $stockAddition = StockAddition::findOrFail($request->stock_addition_id);

        // Check if requested quantity is available (considering current production)
        $currentProduction = $dailyProduction->total_pieces;
        $availableAfterRestore = $stockAddition->available_pieces + $currentProduction;

        if ($request->total_pieces > $availableAfterRestore) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Requested production quantity exceeds available stock.');
        }

        $dailyProduction->update($request->all());

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyProduction $dailyProduction)
    {
        $dailyProduction->delete();

        return redirect()->route('stock-management.daily-production.index')
            ->with('success', 'Daily production deleted successfully.');
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
