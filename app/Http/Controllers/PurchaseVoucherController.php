<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\PurchaseVoucher;
use App\Models\VendorBill;
use App\Models\JournalEntry;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseVoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseVoucher::with(['bill.account', 'stockAddition']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $vouchers = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('accounting.purchase-vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        // Get all accounts payable accounts, including those linked to vendors
        $payableAccounts = ChartOfAccount::active()
            ->where('account_subtype', 'ACCOUNTS_PAYABLE')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        // Also get vendors and their linked accounts
        $vendors = \App\Models\MineVendor::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Prepare accounts array for JavaScript
        $accounts = $payableAccounts->map(function($acc) {
            return [
                'id' => $acc->id,
                'code' => $acc->account_code,
                'name' => $acc->account_name
            ];
        })->toArray();

        // Add vendor accounts
        foreach ($vendors as $vendor) {
            if ($vendor->chartOfAccount) {
                $accounts[] = [
                    'id' => $vendor->chartOfAccount->id,
                    'code' => $vendor->chartOfAccount->account_code,
                    'name' => $vendor->name . ' (Vendor)'
                ];
            }
        }

        $nextVoucherNumber = PurchaseVoucher::generateVoucherNumber();

        return view('accounting.purchase-vouchers.create', [
            'payableAccounts' => $payableAccounts,
            'vendors' => $vendors,
            'accounts' => $accounts,
            'nextVoucherNumber' => $nextVoucherNumber,
        ]);
    }

    public function edit(PurchaseVoucher $purchaseVoucher)
    {
        if (!$purchaseVoucher->isDraft()) {
            return redirect()
                ->route('accounting.purchase-vouchers.index')
                ->with('error', __('Only draft vouchers can be edited.'));
        }

        $payableAccounts = ChartOfAccount::active()
            ->where('account_subtype', 'ACCOUNTS_PAYABLE')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $vendors = \App\Models\MineVendor::with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Prepare accounts array for JavaScript
        $accounts = $payableAccounts->map(function($acc) {
            return [
                'id' => $acc->id,
                'code' => $acc->account_code,
                'name' => $acc->account_name
            ];
        })->toArray();

        // Add vendor accounts
        foreach ($vendors as $vendor) {
            if ($vendor->chartOfAccount) {
                $accounts[] = [
                    'id' => $vendor->chartOfAccount->id,
                    'code' => $vendor->chartOfAccount->account_code,
                    'name' => $vendor->name . ' (Vendor)'
                ];
            }
        }

        return view('accounting.purchase-vouchers.edit', [
            'voucher' => $purchaseVoucher,
            'payableAccounts' => $payableAccounts,
            'vendors' => $vendors,
            'accounts' => $accounts,
        ]);
    }

    public function update(Request $request, PurchaseVoucher $purchaseVoucher)
    {
        if (!$purchaseVoucher->isDraft()) {
            return redirect()
                ->route('accounting.purchase-vouchers.index')
                ->with('error', __('Only draft vouchers can be edited.'));
        }

        $validated = $request->validate([
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'payable_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'vendor_reference' => ['nullable', 'string', 'max:255'],
            'bill_number' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'particulars' => ['nullable', 'string', 'max:1000'],
            'bill_adjustment' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'post' => ['nullable', 'boolean'],
        ]);

        // Update or create vendor bill
        if ($purchaseVoucher->vendor_bill_id) {
            $bill = $purchaseVoucher->bill;
            $bill->update([
                'chart_of_account_id' => $validated['payable_account_id'],
                'vendor_reference' => $validated['vendor_reference'] ?? null,
                'bill_number' => $validated['bill_number'] ?? null,
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'] ?? null,
                'original_amount' => $validated['total_amount'],
                'balance_amount' => $validated['total_amount'],
                'particulars' => $validated['particulars'] ?? null,
                'bill_adjustment' => $validated['bill_adjustment'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        } else {
            $bill = VendorBill::create([
                'chart_of_account_id' => $validated['payable_account_id'],
                'vendor_reference' => $validated['vendor_reference'] ?? null,
                'bill_number' => $validated['bill_number'] ?? null,
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'] ?? null,
                'original_amount' => $validated['total_amount'],
                'balance_amount' => $validated['total_amount'],
                'status' => 'open',
                'particulars' => $validated['particulars'] ?? null,
                'bill_adjustment' => $validated['bill_adjustment'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            $purchaseVoucher->vendor_bill_id = $bill->id;
        }

        // Check if status is changing to posted
        $wasPosted = $purchaseVoucher->isPosted();
        $willBePosted = $request->has('post');

        // Update purchase voucher
        $purchaseVoucher->update([
            'invoice_reference' => $validated['bill_number'] ?? null,
            'total_amount' => $validated['total_amount'],
            'notes' => $validated['notes'] ?? null,
            'status' => $willBePosted ? 'posted' : 'draft',
        ]);

        // Create journal entry if status changed to posted
        if ($willBePosted && !$wasPosted) {
            DB::transaction(function () use ($purchaseVoucher, $request) {
                $this->createJournalEntry($purchaseVoucher, $request->user()->id);
            });
        }

        $message = $willBePosted
            ? __('Purchase voucher :number posted successfully.', ['number' => $purchaseVoucher->voucher_number])
            : __('Purchase voucher :number updated successfully.', ['number' => $purchaseVoucher->voucher_number]);

        return redirect()
            ->route('accounting.purchase-vouchers.index')
            ->with('success', $message);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'payable_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'vendor_reference' => ['nullable', 'string', 'max:255'],
            'bill_number' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'particulars' => ['nullable', 'string', 'max:1000'],
            'bill_adjustment' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $bill = VendorBill::create([
            'chart_of_account_id' => $validated['payable_account_id'],
            'vendor_reference' => $validated['vendor_reference'] ?? null,
            'bill_number' => $validated['bill_number'] ?? null,
            'bill_date' => $validated['bill_date'],
            'due_date' => $validated['due_date'] ?? null,
            'original_amount' => $validated['total_amount'],
            'balance_amount' => $validated['total_amount'],
            'status' => 'open',
            'particulars' => $validated['particulars'] ?? null,
            'bill_adjustment' => $validated['bill_adjustment'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $voucher = DB::transaction(function () use ($request, $validated, $bill) {
            $voucher = PurchaseVoucher::create([
                'vendor_bill_id' => $bill->id,
                'invoice_reference' => $validated['bill_number'] ?? null,
                'total_amount' => $validated['total_amount'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'posted',
                'created_by' => $request->user()->id,
            ]);

            // Create journal entry and transactions
            $this->createJournalEntry($voucher, $request->user()->id);

            return $voucher;
        });

        return redirect()
            ->route('accounting.purchase-vouchers.index')
            ->with('success', __('Purchase voucher :number recorded successfully.', ['number' => $voucher->voucher_number]));
    }

    /**
     * Display the print view for the voucher.
     */
    public function print(PurchaseVoucher $purchaseVoucher)
    {
        $purchaseVoucher->load([
            'bill.account',
            'creator',
        ]);

        return view('accounting.purchase-vouchers.print', compact('purchaseVoucher'));
    }

    /**
     * Create journal entry and transactions from purchase voucher.
     */
    protected function createJournalEntry(PurchaseVoucher $voucher, ?int $userId): void
    {
        // Check if journal entry already exists for this voucher
        $existingTransaction = AccountTransaction::where('reference_type', 'purchase_voucher')
            ->where('reference_id', $voucher->id)
            ->first();

        if ($existingTransaction) {
            \Log::info("Journal entry already exists for purchase voucher #{$voucher->id}");
            return;
        }

        $voucher->load(['bill.account', 'stockAddition']);

        if (!$voucher->bill || !$voucher->bill->account) {
            \Log::warning("Purchase voucher #{$voucher->id} missing bill or account");
            return;
        }

        // Get the accounts payable account (credit side)
        $payableAccount = $voucher->bill->account;

        // Determine the debit account
        // If linked to stock addition, use inventory account (1130)
        // Otherwise, use a purchase expense account or inventory as default
        $inventoryAccount = ChartOfAccount::where('account_code', '1130')->first(); // Particulars Inventory
        
        if (!$inventoryAccount) {
            \Log::error('Inventory account (1130) not found for purchase voucher journal entry');
            return;
        }

        $debitAccount = $inventoryAccount;
        $amount = $voucher->total_amount;

        // Create journal entry
        $journalEntry = JournalEntry::create([
            'entry_date' => $voucher->bill->bill_date,
            'description' => 'Purchase Voucher: ' . $voucher->voucher_number . ($voucher->bill->bill_number ? ' - Bill: ' . $voucher->bill->bill_number : ''),
            'entry_type' => 'AUTO_PURCHASE',
            'total_debit' => $amount,
            'total_credit' => $amount,
            'status' => 'POSTED',
            'created_by' => $userId,
            'posted_at' => now(),
            'notes' => $voucher->notes ?? $voucher->bill->notes,
        ]);

        // Create debit transaction (Inventory/Purchase)
        AccountTransaction::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $debitAccount->id,
            'debit_amount' => $amount,
            'credit_amount' => 0,
            'description' => $voucher->bill->particulars ?? 'Purchase from vendor',
            'reference_type' => 'purchase_voucher',
            'reference_id' => $voucher->id,
        ]);

        // Create credit transaction (Accounts Payable)
        AccountTransaction::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccount->id,
            'debit_amount' => 0,
            'credit_amount' => $amount,
            'description' => 'Accounts Payable: ' . ($voucher->bill->bill_number ?? $voucher->voucher_number),
            'reference_type' => 'purchase_voucher',
            'reference_id' => $voucher->id,
        ]);

        // Update account balances
        $debitAccount->updateBalance();
        $payableAccount->updateBalance();

        \Log::info("Journal entry created for purchase voucher #{$voucher->id}");
    }
}

