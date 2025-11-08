<?php

namespace App\Http\Controllers;

use App\Models\BankPaymentVoucher;
use App\Models\MineVendor;
use Illuminate\Http\Request;

class BankPaymentVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = BankPaymentVoucher::with(['vendor'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('accounting.bank-payment-vouchers.index', compact('vouchers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = MineVendor::orderBy('name')->get();

        return view('accounting.bank-payment-vouchers.create', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'vendor_id' => ['required', 'exists:mine_vendors,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $voucher = BankPaymentVoucher::create([
            'payment_date' => $validated['payment_date'],
            'vendor_id' => $validated['vendor_id'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('accounting.bank-payment-vouchers.show', $voucher)
            ->with('success', 'Bank payment voucher created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankPaymentVoucher $bankPaymentVoucher)
    {
        $bankPaymentVoucher->load(['vendor', 'creator']);

        return view('accounting.bank-payment-vouchers.show', compact('bankPaymentVoucher'));
    }
}

