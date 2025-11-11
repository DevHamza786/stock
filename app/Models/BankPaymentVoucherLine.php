<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankPaymentVoucherLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_payment_voucher_id',
        'chart_of_account_id',
        'entry_type',
        'amount',
        'particulars',
        'cheque_no',
        'cheque_date',
        'bill_adjustment',
        'vendor_bill_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cheque_date' => 'date',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(BankPaymentVoucher::class, 'bank_payment_voucher_id');
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
        return $this->hasMany(VendorBillPayment::class, 'bank_payment_voucher_line_id');
    }
}

