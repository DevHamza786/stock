<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashPaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'payment_date',
        'cash_account_id',
        'amount',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
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
        $prefix = "CPV-{$year}-";

        $lastVoucher = self::where('voucher_number', 'like', "{$prefix}%")
            ->orderBy('voucher_number', 'desc')
            ->first();

        $number = $lastVoucher
            ? ((int) substr($lastVoucher->voucher_number, -5)) + 1
            : 1;

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_account_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CashPaymentVoucherLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

