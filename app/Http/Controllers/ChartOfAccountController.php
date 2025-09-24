<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();

        // Filter by account type
        if ($request->filled('account_type')) {
            $query->byType($request->account_type);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        } else {
            $query->active();
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                  ->orWhere('account_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('account_code')->paginate(20);

        $accountTypes = ChartOfAccount::getAccountTypes();

        return view('accounting.chart-of-accounts.index', compact('accounts', 'accountTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accountTypes = ChartOfAccount::getAccountTypes();
        $accountSubtypes = ChartOfAccount::getAccountSubtypes();
        $parentAccounts = ChartOfAccount::active()->orderBy('account_code')->get();

        return view('accounting.chart-of-accounts.create', compact('accountTypes', 'accountSubtypes', 'parentAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_type' => ['required', Rule::in(array_keys(ChartOfAccount::getAccountTypes()))],
            'account_subtype' => ['required', Rule::in(array_keys(ChartOfAccount::getAccountSubtypes()))],
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'level' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
            'is_system_account' => 'boolean',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_system_account'] = $request->boolean('is_system_account', false);

        ChartOfAccount::create($validated);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['parentAccount', 'childAccounts', 'transactions.journalEntry']);

        return view('accounting.chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        $accountTypes = ChartOfAccount::getAccountTypes();
        $accountSubtypes = ChartOfAccount::getAccountSubtypes();
        $parentAccounts = ChartOfAccount::active()
            ->where('id', '!=', $chartOfAccount->id)
            ->orderBy('account_code')
            ->get();

        return view('accounting.chart-of-accounts.edit', compact('chartOfAccount', 'accountTypes', 'accountSubtypes', 'parentAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $chartOfAccount->id,
            'account_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_type' => ['required', Rule::in(array_keys(ChartOfAccount::getAccountTypes()))],
            'account_subtype' => ['required', Rule::in(array_keys(ChartOfAccount::getAccountSubtypes()))],
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'level' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
            'is_system_account' => 'boolean',
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_system_account'] = $request->boolean('is_system_account', false);

        $chartOfAccount->update($validated);

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Check if account has transactions
        if ($chartOfAccount->transactions()->exists()) {
            return redirect()->route('accounting.chart-of-accounts.index')
                ->with('error', 'Cannot delete account with existing transactions.');
        }

        // Check if account has child accounts
        if ($chartOfAccount->childAccounts()->exists()) {
            return redirect()->route('accounting.chart-of-accounts.index')
                ->with('error', 'Cannot delete account with child accounts.');
        }

        $chartOfAccount->delete();

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    /**
     * Toggle account active status.
     */
    public function toggleStatus(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->update(['is_active' => !$chartOfAccount->is_active]);

        $status = $chartOfAccount->is_active ? 'activated' : 'deactivated';

        return redirect()->route('accounting.chart-of-accounts.index')
            ->with('success', "Account {$status} successfully.");
    }

    /**
     * Get accounts for dropdown/select.
     */
    public function getAccounts(Request $request)
    {
        $query = ChartOfAccount::active();

        if ($request->filled('account_type')) {
            $query->byType($request->account_type);
        }

        if ($request->filled('normal_balance')) {
            $query->where('normal_balance', $request->normal_balance);
        }

        $accounts = $query->orderBy('account_code')->get();

        return response()->json($accounts);
    }

    /**
     * Update account balance.
     */
    public function updateBalance(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->updateBalance();

        return response()->json([
            'success' => true,
            'current_balance' => $chartOfAccount->current_balance,
            'message' => 'Account balance updated successfully.'
        ]);
    }
}
