<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorBillPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_bill_id',
        'bank_payment_voucher_line_id',
        'cash_payment_voucher_line_id',
        'amount',
        'applied_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_at' => 'date',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function voucherLine(): BelongsTo
    {
        return $this->belongsTo(BankPaymentVoucherLine::class, 'bank_payment_voucher_line_id');
    }

    public function cashVoucherLine(): BelongsTo
    {
        return $this->belongsTo(CashPaymentVoucherLine::class, 'cash_payment_voucher_line_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

