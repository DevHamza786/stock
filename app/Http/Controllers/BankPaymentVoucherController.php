<?php

namespace App\Http\Controllers;

use App\Models\BankPaymentVoucher;
use App\Models\BankPaymentVoucherLine;
use App\Models\ChartOfAccount;
use App\Models\VendorBill;
use App\Models\VendorBillPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankPaymentVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BankPaymentVoucher::with(['bankAccount']);

        // Filter by voucher type (bank/cash/all)
        if ($request->filled('voucher_type')) {
            $voucherType = $request->get('voucher_type');
            if ($voucherType === 'bank') {
                // Only bank payment vouchers
                // (this is already the default, but we can add additional filtering if needed)
            } elseif ($voucherType === 'cash') {
                // This would need to be handled in a unified payment voucher controller
                // For now, we'll just show bank vouchers
            }
        }

        // Filter by bank account
        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->get('bank_account_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->get('date_to'));
        }

        $vouchers = $query->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $bankAccounts = \App\Models\ChartOfAccount::active()
            ->where('account_type', 'ASSET')
            ->where('account_subtype', 'CASH')
            ->where(function($query) {
                $query->whereRaw('LOWER(account_name) LIKE ?', ['%bank%'])
                      ->orWhere('account_code', 'like', '15%');
            })
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        return view('accounting.bank-payment-vouchers.index', compact('vouchers', 'bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $voucherType = $request->get('type', 'payment'); // payment or receipt
        $nextVoucherNumber = BankPaymentVoucher::generateVoucherNumber($voucherType);

        // Get bank accounts - all accounts with CASH subtype that have "bank" in name or account_code starting with 15xx
        // This includes accounts like "Bank", "Meezan Bank Site Branch", etc. from Chart of Accounts
        $bankAccounts = ChartOfAccount::active()
            ->where('account_type', 'ASSET')
            ->where('account_subtype', 'CASH')
            ->where(function($query) {
                // Include accounts with "bank" in the name (case insensitive)
                $query->whereRaw('LOWER(account_name) LIKE ?', ['%bank%'])
                      // Or accounts with account_code starting with 15xx (bank account range)
                      ->orWhere('account_code', 'like', '15%');
            })
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'account_subtype']);

        // Get all accounts, including accounts payable linked to vendors
        $accounts = ChartOfAccount::active()
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'account_type', 'account_subtype']);

        // Get vendors and their linked accounts - prioritize vendors with chart_of_account_id
        $vendors = \App\Models\MineVendor::with('chartOfAccount')
            ->where('is_active', true)
            ->whereNotNull('chart_of_account_id')
            ->orderBy('name')
            ->get();

        // Also get accounts payable accounts
        $payableAccounts = ChartOfAccount::active()
            ->where('account_subtype', 'ACCOUNTS_PAYABLE')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $vendorBills = VendorBill::with('account')
            ->open()
            ->orderBy('bill_date')
            ->get()
            ->groupBy('chart_of_account_id');

        return view('accounting.bank-payment-vouchers.create', [
            'nextVoucherNumber' => $nextVoucherNumber,
            'voucherType' => $voucherType,
            'bankAccounts' => $bankAccounts,
            'accounts' => $accounts,
            'vendors' => $vendors,
            'payableAccounts' => $payableAccounts,
            'vendorBills' => $vendorBills,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'voucher_type' => ['required', 'in:payment,receipt'],
            'payment_date' => ['required', 'date'],
            'bank_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.type' => ['required', 'in:Dr,Cr'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
            'lines.*.particulars' => ['nullable', 'string', 'max:1000'],
            'lines.*.cheque_no' => ['nullable', 'string', 'max:100'],
            'lines.*.cheque_date' => ['nullable', 'date'],
            'lines.*.bill_id' => ['nullable', 'exists:vendor_bills,id'],
            'lines.*.bill_amount' => ['nullable', 'numeric', 'min:0.01'],
            'lines.*.bill_adjustment' => ['nullable', 'string', 'max:255'],
        ]);

        $lineItems = collect($validated['lines'])
            ->map(function (array $line) {
                $line['amount'] = (float) $line['amount'];

                return $line;
            })
            ->filter(fn (array $line) => $line['amount'] > 0);

        if ($lineItems->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => __('At least one ledger line is required.'),
            ]);
        }

        $totals = $this->calculateTotals($lineItems);
        $voucherType = $validated['voucher_type'];

        // For payment: debits must exceed credits (netBankAmount > 0)
        // For receipt: credits must exceed debits (netBankAmount < 0, so we check netReceiptAmount > 0)
        $netAmount = $voucherType === 'payment' 
            ? $totals['debitTotal'] - $totals['creditTotal']
            : $totals['creditTotal'] - $totals['debitTotal'];

        if ($netAmount <= 0) {
            $message = $voucherType === 'payment'
                ? __('Total debits must exceed credits to create a bank payment.')
                : __('Total credits must exceed debits to create a bank receipt.');
            throw ValidationException::withMessages([
                'lines' => $message,
            ]);
        }

        $bankAccount = ChartOfAccount::findOrFail($validated['bank_account_id']);
        
        // Validate that the selected account is actually a BANK account, not CASH
        if ($bankAccount->account_subtype !== 'BANK') {
            throw ValidationException::withMessages([
                'bank_account_id' => __('The selected account must be a bank account. For petty cash or cash accounts, please use Cash Payment Vouchers.'),
            ]);
        }
        
        $voucherAmount = $voucherType === 'payment' 
            ? $totals['debitTotal'] - $totals['creditTotal']
            : $totals['creditTotal'] - $totals['debitTotal'];

        $voucher = DB::transaction(function () use ($request, $validated, $lineItems, $totals, $bankAccount, $voucherType, $voucherAmount) {
            $voucher = BankPaymentVoucher::create([
                'voucher_type' => $voucherType,
                'voucher_number' => BankPaymentVoucher::generateVoucherNumber($voucherType),
                'payment_date' => $validated['payment_date'],
                'bank_account_id' => $bankAccount->id,
                'amount' => $voucherAmount,
                'payment_method' => $bankAccount->account_code . ' — ' . $bankAccount->account_name,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($lineItems as $line) {
                $voucherLine = $this->createVoucherLine($voucher, $line, $request->user()->id);

                if (! empty($line['bill_id'])) {
                    $this->applyBill(
                        $voucherLine,
                        (int) $line['bill_id'],
                        $line['bill_amount'] ?? $line['amount'],
                        $request->user()->id,
                        $voucher->payment_date
                    );
                }
            }

            // For payment voucher: bank is credited (Cr)
            // For receipt voucher: bank is debited (Dr)
            $bankEntryType = $voucherType === 'payment' ? 'Cr' : 'Dr';
            $bankParticulars = $voucherType === 'payment' ? __('Bank payment') : __('Bank receipt');

            $voucher->lines()->create([
                'chart_of_account_id' => $bankAccount->id,
                'vendor_bill_id' => null,
                'entry_type' => $bankEntryType,
                'amount' => $voucherAmount,
                'particulars' => $bankParticulars,
                'cheque_no' => null,
                'cheque_date' => null,
                'bill_adjustment' => null,
            ]);

            // Create journal entry and transactions
            $this->createJournalEntry($voucher, $request->user()->id);

            return $voucher;
        });

        return redirect()
            ->route('accounting.bank-payment-vouchers.show', $voucher)
            ->with('success', __('Bank payment voucher created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(BankPaymentVoucher $bankPaymentVoucher)
    {
        $bankPaymentVoucher->load([
            'bankAccount',
            'creator',
            'lines.account',
            'lines.billPayments.bill',
        ]);

        return view('accounting.bank-payment-vouchers.show', compact('bankPaymentVoucher'));
    }

    /**
     * Display the print view for the voucher.
     */
    public function print(BankPaymentVoucher $bankPaymentVoucher)
    {
        $bankPaymentVoucher->load([
            'bankAccount',
            'creator',
            'lines.account',
            'lines.billPayments.bill',
        ]);

        $debitLines = $bankPaymentVoucher->lines->where('entry_type', 'Dr')->sortBy('account.account_code');
        $creditLines = $bankPaymentVoucher->lines->where('entry_type', 'Cr')->sortBy('account.account_code');

        return view('accounting.bank-payment-vouchers.print', compact('bankPaymentVoucher', 'debitLines', 'creditLines'));
    }

    /**
     * Calculate totals for voucher lines.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $lines
     */
    protected function calculateTotals(Collection $lines): array
    {
        $debitTotal = $lines
            ->where('type', 'Dr')
            ->sum('amount');

        $creditTotal = $lines
            ->where('type', 'Cr')
            ->sum('amount');

        return [
            'debitTotal' => $debitTotal,
            'creditTotal' => $creditTotal,
            'netBankAmount' => $debitTotal - $creditTotal,
        ];
    }

    protected function createVoucherLine(BankPaymentVoucher $voucher, array $line, ?int $userId = null): BankPaymentVoucherLine
    {
        return $voucher->lines()->create([
            'chart_of_account_id' => $line['account_id'],
            'vendor_bill_id' => $line['bill_id'] ?? null,
            'entry_type' => $line['type'],
            'amount' => $line['amount'],
            'particulars' => $line['particulars'] ?? null,
            'cheque_no' => $line['cheque_no'] ?? null,
            'cheque_date' => $line['cheque_date'] ?? null,
            'bill_adjustment' => $line['bill_adjustment'] ?? null,
        ]);
    }

    protected function applyBill(
        BankPaymentVoucherLine $voucherLine,
        int $billId,
        float $applyAmount,
        ?int $userId,
        ?string $appliedDate = null
    ): void {
        $bill = VendorBill::lockForUpdate()->findOrFail($billId);

        if ($bill->chart_of_account_id !== $voucherLine->chart_of_account_id) {
            throw ValidationException::withMessages([
                'lines' => __('Selected bill does not belong to the chosen ledger.'),
            ]);
        }

        if ($voucherLine->entry_type !== 'Dr') {
            throw ValidationException::withMessages([
                'lines' => __('Only debit lines can be applied to vendor bills.'),
            ]);
        }

        if ($applyAmount > $voucherLine->amount) {
            throw ValidationException::withMessages([
                'lines' => __('Applied amount cannot exceed the ledger line amount.'),
            ]);
        }

        if ($applyAmount > $bill->balance_amount) {
            throw ValidationException::withMessages([
                'lines' => __('Applied amount cannot exceed the outstanding bill balance.'),
            ]);
        }

        VendorBillPayment::create([
            'vendor_bill_id' => $bill->id,
            'bank_payment_voucher_line_id' => $voucherLine->id,
            'amount' => $applyAmount,
            'applied_at' => $appliedDate ?? now(),
            'created_by' => $userId,
        ]);

        $bill->applyPayment($applyAmount);
    }

    /**
     * Create journal entry and transactions from voucher.
     */
    protected function createJournalEntry(BankPaymentVoucher $voucher, ?int $userId): void
    {
        // Check if journal entry already exists for this voucher
        $existingTransaction = \App\Models\AccountTransaction::where('reference_type', 'bank_payment_voucher')
            ->where('reference_id', $voucher->id)
            ->first();

        if ($existingTransaction) {
            \Log::info("Journal entry already exists for bank payment voucher #{$voucher->id}");
            return;
        }

        $voucher->load('lines.account');

        // Calculate totals
        $totalDebit = $voucher->lines->where('entry_type', 'Dr')->sum('amount');
        $totalCredit = $voucher->lines->where('entry_type', 'Cr')->sum('amount');

        // Create journal entry
        $journalEntry = \App\Models\JournalEntry::create([
            'entry_date' => $voucher->payment_date,
            'description' => ($voucher->isPayment() ? 'Bank Payment' : 'Bank Receipt') . ' Voucher: ' . $voucher->voucher_number,
            'entry_type' => 'MANUAL',
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'POSTED',
            'created_by' => $userId,
            'posted_at' => now(),
            'notes' => $voucher->notes,
        ]);

        // Create account transactions from voucher lines
        foreach ($voucher->lines as $line) {
            \App\Models\AccountTransaction::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $line->chart_of_account_id,
                'debit_amount' => $line->entry_type === 'Dr' ? $line->amount : 0,
                'credit_amount' => $line->entry_type === 'Cr' ? $line->amount : 0,
                'description' => $line->particulars ?? ($voucher->isPayment() ? 'Bank Payment' : 'Bank Receipt'),
                'reference_type' => 'bank_payment_voucher',
                'reference_id' => $voucher->id,
            ]);

            // Update account balance
            $line->account->updateBalance();
        }
    }
}
