<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\PurchaseVoucher;
use App\Models\VendorBill;
use Illuminate\Http\Request;

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

        // Update purchase voucher
        $purchaseVoucher->update([
            'invoice_reference' => $validated['bill_number'] ?? null,
            'total_amount' => $validated['total_amount'],
            'notes' => $validated['notes'] ?? null,
            'status' => $request->has('post') ? 'posted' : 'draft',
        ]);

        $message = $request->has('post')
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

        $voucher = PurchaseVoucher::create([
            'vendor_bill_id' => $bill->id,
            'invoice_reference' => $validated['bill_number'] ?? null,
            'total_amount' => $validated['total_amount'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'posted',
            'created_by' => $request->user()->id,
        ]);

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
}

