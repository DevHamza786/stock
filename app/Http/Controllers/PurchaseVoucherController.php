<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\PurchaseVoucher;
use App\Models\VendorBill;
use Illuminate\Http\Request;

class PurchaseVoucherController extends Controller
{
    public function index()
    {
        $vouchers = PurchaseVoucher::with(['bill.account'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('accounting.purchase-vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $payableAccounts = ChartOfAccount::active()
            ->where('account_subtype', 'ACCOUNTS_PAYABLE')
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        $nextVoucherNumber = PurchaseVoucher::generateVoucherNumber();

        return view('accounting.purchase-vouchers.create', [
            'payableAccounts' => $payableAccounts,
            'nextVoucherNumber' => $nextVoucherNumber,
        ]);
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
            'notes' => $validated['notes'] ?? null,
        ]);

        $voucher = PurchaseVoucher::create([
            'vendor_bill_id' => $bill->id,
            'invoice_reference' => $validated['bill_number'] ?? null,
            'total_amount' => $validated['total_amount'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('accounting.purchase-vouchers.index')
            ->with('success', __('Purchase voucher :number recorded successfully.', ['number' => $voucher->voucher_number]));
    }
}

