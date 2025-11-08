<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JournalEntry::with(['creator', 'transactions.account']);

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by entry type
        if ($request->filled('entry_type')) {
            $query->byType($request->entry_type);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search by entry number or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journalEntries = $query->orderBy('created_at', 'desc')->paginate(20);

        $statuses = JournalEntry::getStatuses();
        $entryTypes = JournalEntry::getEntryTypes();

        return view('accounting.journal-entries.index', compact('journalEntries', 'statuses', 'entryTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::active()->orderBy('account_code')->get();
        $entryTypes = JournalEntry::getEntryTypes();

        return view('accounting.journal-entries.create', compact('accounts', 'entryTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'entry_type' => ['required', Rule::in(array_keys(JournalEntry::getEntryTypes()))],
            'notes' => 'nullable|string',
            'transactions' => 'required|array|min:2',
            'transactions.*.account_id' => 'required|exists:chart_of_accounts,id',
            'transactions.*.debit_amount' => 'required|numeric|min:0',
            'transactions.*.credit_amount' => 'required|numeric|min:0',
            'transactions.*.description' => 'required|string|max:255',
        ]);

        // Validate that at least one debit and one credit transaction exists
        $hasDebit = collect($validated['transactions'])->some(function($transaction) {
            return $transaction['debit_amount'] > 0;
        });

        $hasCredit = collect($validated['transactions'])->some(function($transaction) {
            return $transaction['credit_amount'] > 0;
        });

        if (!$hasDebit || !$hasCredit) {
            return back()->withErrors(['transactions' => 'At least one debit and one credit transaction is required.']);
        }

        // Calculate totals
        $totalDebit = collect($validated['transactions'])->sum('debit_amount');
        $totalCredit = collect($validated['transactions'])->sum('credit_amount');

        // Validate that debits equal credits
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['transactions' => 'Total debits must equal total credits.']);
        }

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_date' => $validated['entry_date'],
            'description' => $validated['description'],
            'entry_type' => $validated['entry_type'],
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create transactions
        foreach ($validated['transactions'] as $transactionData) {
            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $transactionData['account_id'],
                'debit_amount' => $transactionData['debit_amount'],
                'credit_amount' => $transactionData['credit_amount'],
                'description' => $transactionData['description'],
            ]);
        }

        return redirect()->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['creator', 'approver', 'transactions.account']);

        return view('accounting.journal-entries.show', compact('journalEntry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'POSTED') {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Cannot edit posted journal entry.');
        }

        $journalEntry->load(['transactions.account']);
        $accounts = ChartOfAccount::active()->orderBy('account_code')->get();
        $entryTypes = JournalEntry::getEntryTypes();
        $transactionFormData = $journalEntry->transactions->map(function ($transaction) {
            return [
                'account_id' => $transaction->account_id,
                'description' => $transaction->description,
                'debit_amount' => $transaction->debit_amount,
                'credit_amount' => $transaction->credit_amount,
            ];
        })->values()->all();

        return view('accounting.journal-entries.edit', compact('journalEntry', 'accounts', 'entryTypes', 'transactionFormData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'POSTED') {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Cannot edit posted journal entry.');
        }

        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'entry_type' => ['required', Rule::in(array_keys(JournalEntry::getEntryTypes()))],
            'notes' => 'nullable|string',
            'transactions' => 'required|array|min:2',
            'transactions.*.account_id' => 'required|exists:chart_of_accounts,id',
            'transactions.*.debit_amount' => 'required|numeric|min:0',
            'transactions.*.credit_amount' => 'required|numeric|min:0',
            'transactions.*.description' => 'required|string|max:255',
        ]);

        // Validate that at least one debit and one credit transaction exists
        $hasDebit = collect($validated['transactions'])->some(function($transaction) {
            return $transaction['debit_amount'] > 0;
        });

        $hasCredit = collect($validated['transactions'])->some(function($transaction) {
            return $transaction['credit_amount'] > 0;
        });

        if (!$hasDebit || !$hasCredit) {
            return back()->withErrors(['transactions' => 'At least one debit and one credit transaction is required.']);
        }

        // Calculate totals
        $totalDebit = collect($validated['transactions'])->sum('debit_amount');
        $totalCredit = collect($validated['transactions'])->sum('credit_amount');

        // Validate that debits equal credits
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['transactions' => 'Total debits must equal total credits.']);
        }

        // Update journal entry
        $journalEntry->update([
            'entry_date' => $validated['entry_date'],
            'description' => $validated['description'],
            'entry_type' => $validated['entry_type'],
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Delete existing transactions
        $journalEntry->transactions()->delete();

        // Create new transactions
        foreach ($validated['transactions'] as $transactionData) {
            AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $transactionData['account_id'],
                'debit_amount' => $transactionData['debit_amount'],
                'credit_amount' => $transactionData['credit_amount'],
                'description' => $transactionData['description'],
            ]);
        }

        return redirect()->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'POSTED') {
            return redirect()->route('accounting.journal-entries.index')
                ->with('error', 'Cannot delete posted journal entry.');
        }

        $journalEntry->delete();

        return redirect()->route('accounting.journal-entries.index')
            ->with('success', 'Journal entry deleted successfully.');
    }

    /**
     * Post the journal entry.
     */
    public function post(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'DRAFT') {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Only draft entries can be posted.');
        }

        if (!$journalEntry->isBalanced()) {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Journal entry is not balanced.');
        }

        $journalEntry->post();

        return redirect()->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry posted successfully.');
    }

    /**
     * Reverse the journal entry.
     */
    public function reverse(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'POSTED') {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Only posted entries can be reversed.');
        }

        $journalEntry->reverse();

        return redirect()->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry reversed successfully.');
    }

    /**
     * Approve the journal entry.
     */
    public function approve(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'DRAFT') {
            return redirect()->route('accounting.journal-entries.show', $journalEntry)
                ->with('error', 'Only draft entries can be approved.');
        }

        $journalEntry->update(['approved_by' => auth()->id()]);

        return redirect()->route('accounting.journal-entries.show', $journalEntry)
            ->with('success', 'Journal entry approved successfully.');
    }

    /**
     * Get journal entry details for API.
     */
    public function getDetails(JournalEntry $journalEntry)
    {
        $journalEntry->load(['transactions.account']);

        return response()->json([
            'journal_entry' => $journalEntry,
            'transactions' => $journalEntry->transactions,
            'is_balanced' => $journalEntry->isBalanced(),
        ]);
    }
}
