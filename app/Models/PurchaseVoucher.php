<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'vendor_bill_id',
        'invoice_reference',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $voucher) {
            if (empty($voucher->voucher_number)) {
                $voucher->voucher_number = self::generateVoucherNumber();
            }

            if (empty($voucher->created_by) && auth()->check()) {
                $voucher->created_by = auth()->id();
            }
        });
    }

    public static function generateVoucherNumber(): string
    {
        $year = date('Y');
        $prefix = "PBV-{$year}-";

        $lastVoucher = self::where('voucher_number', 'like', "{$prefix}%")
            ->orderBy('voucher_number', 'desc')
            ->first();

        $number = $lastVoucher
            ? ((int) substr($lastVoucher->voucher_number, -5)) + 1
            : 1;

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

