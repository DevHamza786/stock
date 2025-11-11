<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashPaymentVoucherLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_payment_voucher_id',
        'chart_of_account_id',
        'vendor_bill_id',
        'entry_type',
        'amount',
        'particulars',
        'bill_adjustment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(CashPaymentVoucher::class, 'cash_payment_voucher_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function vendorBill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function billPayments(): HasMany
    {
        return $this->hasMany(VendorBillPayment::class, 'cash_payment_voucher_line_id');
    }
}

