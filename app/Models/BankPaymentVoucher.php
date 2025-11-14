<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankPaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'payment_date',
        'bank_account_id',
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

    public static function generateVoucherNumber(string $type = 'payment'): string
    {
        $year = date('Y');
        // BPV for bank payment, BRV for bank receipt
        $prefix = $type === 'receipt' ? "BRV-{$year}-" : "BPV-{$year}-";

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
    
    public function isPayment(): bool
    {
        return $this->voucher_type === 'payment';
    }
    
    public function isReceipt(): bool
    {
        return $this->voucher_type === 'receipt';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'bank_account_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankPaymentVoucherLine::class);
    }
}

