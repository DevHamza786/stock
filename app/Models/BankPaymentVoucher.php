<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankPaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'payment_date',
        'vendor_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (BankPaymentVoucher $voucher) {
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
        $prefix = "BPV-{$year}-";

        $lastVoucher = self::where('voucher_number', 'like', $prefix . '%')
            ->orderBy('voucher_number', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->voucher_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(MineVendor::class, 'vendor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

