<?php

namespace App\Http\Controllers;

use App\Models\CashPaymentVoucher;
use App\Models\CashPaymentVoucherLine;
use App\Models\ChartOfAccount;
use App\Models\VendorBill;
use App\Models\VendorBillPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashPaymentVoucherController extends Controller
{
    public function index()
    {
        $vouchers = CashPaymentVoucher::with(['cashAccount'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('accounting.cash-payment-vouchers.index', compact('vouchers'));
    }

    /**
     * Display the print view for the voucher.
     */
    public function print(CashPaymentVoucher $cashPaymentVoucher)
    {
        $cashPaymentVoucher->load([
            'cashAccount',
            'creator',
            'lines.account',
        ]);

        $debitLines = $cashPaymentVoucher->lines->where('entry_type', 'Dr')->sortBy('account.account_code');
        $creditLines = $cashPaymentVoucher->lines->where('entry_type', 'Cr')->sortBy('account.account_code');

        return view('accounting.cash-payment-vouchers.print', compact('cashPaymentVoucher', 'debitLines', 'creditLines'));
    }

    public function create()
    {
        $nextVoucherNumber = CashPaymentVoucher::generateVoucherNumber();

        $cashAccounts = ChartOfAccount::active()
            ->where('account_subtype', 'CASH')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $accounts = ChartOfAccount::active()
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name', 'account_type', 'account_subtype']);

        $vendorBills = VendorBill::with('account')
            ->open()
            ->orderBy('bill_date')
            ->get()
            ->groupBy('chart_of_account_id');

        return view('accounting.cash-payment-vouchers.create', [
            'nextVoucherNumber' => $nextVoucherNumber,
            'cashAccounts' => $cashAccounts,
            'accounts' => $accounts,
            'vendorBills' => $vendorBills,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'cash_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.type' => ['required', 'in:Dr,Cr'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
            'lines.*.particulars' => ['nullable', 'string', 'max:1000'],
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

        if ($totals['netCashAmount'] <= 0) {
            throw ValidationException::withMessages([
                'lines' => __('Total debits must exceed credits to create a cash payment.'),
            ]);
        }

        $cashAccount = ChartOfAccount::findOrFail($validated['cash_account_id']);

        $voucher = DB::transaction(function () use ($request, $validated, $lineItems, $totals, $cashAccount) {
            $voucher = CashPaymentVoucher::create([
                'payment_date' => $validated['payment_date'],
                'cash_account_id' => $cashAccount->id,
                'amount' => $totals['netCashAmount'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($lineItems as $line) {
                $voucherLine = $voucher->lines()->create([
                    'chart_of_account_id' => $line['account_id'],
                    'vendor_bill_id' => $line['bill_id'] ?? null,
                    'entry_type' => $line['type'],
                    'amount' => $line['amount'],
                    'particulars' => $line['particulars'] ?? null,
                    'bill_adjustment' => $line['bill_adjustment'] ?? null,
                ]);

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

            $voucher->lines()->create([
                'chart_of_account_id' => $cashAccount->id,
                'entry_type' => 'Cr',
                'amount' => $totals['netCashAmount'],
                'particulars' => __('Cash payment'),
            ]);

            // Create journal entry and transactions
            $this->createJournalEntry($voucher, $request->user()->id);

            return $voucher;
        });

        return redirect()
            ->route('accounting.cash-payment-vouchers.index')
            ->with('success', __('Cash payment voucher :number recorded successfully.', ['number' => $voucher->voucher_number]));
    }

    protected function calculateTotals(Collection $lines): array
    {
        $debitTotal = $lines->where('type', 'Dr')->sum('amount');
        $creditTotal = $lines->where('type', 'Cr')->sum('amount');

        return [
            'debitTotal' => $debitTotal,
            'creditTotal' => $creditTotal,
            'netCashAmount' => $debitTotal - $creditTotal,
        ];
    }

    protected function applyBill(
        CashPaymentVoucherLine $voucherLine,
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
            'cash_payment_voucher_line_id' => $voucherLine->id,
            'amount' => $applyAmount,
            'applied_at' => $appliedDate,
            'created_by' => $userId,
        ]);

        $bill->applyPayment($applyAmount);
    }

    /**
     * Create journal entry and transactions from voucher.
     */
    protected function createJournalEntry(CashPaymentVoucher $voucher, ?int $userId): void
    {
        // Check if journal entry already exists for this voucher
        $existingTransaction = \App\Models\AccountTransaction::where('reference_type', 'cash_payment_voucher')
            ->where('reference_id', $voucher->id)
            ->first();

        if ($existingTransaction) {
            \Log::info("Journal entry already exists for cash payment voucher #{$voucher->id}");
            return;
        }

        $voucher->load('lines.account');

        // Calculate totals
        $totalDebit = $voucher->lines->where('entry_type', 'Dr')->sum('amount');
        $totalCredit = $voucher->lines->where('entry_type', 'Cr')->sum('amount');

        // Create journal entry
        $journalEntry = \App\Models\JournalEntry::create([
            'entry_date' => $voucher->payment_date,
            'description' => 'Cash Payment Voucher: ' . $voucher->voucher_number,
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
                'description' => $line->particulars ?? 'Cash Payment',
                'reference_type' => 'cash_payment_voucher',
                'reference_id' => $voucher->id,
            ]);

            // Update account balance
            $line->account->updateBalance();
        }
    }
}

