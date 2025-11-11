<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VendorBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_of_account_id',
        'vendor_reference',
        'bill_number',
        'bill_date',
        'due_date',
        'original_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'original_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(VendorBillPayment::class);
    }

    public function purchaseVoucher(): HasOne
    {
        return $this->hasOne(PurchaseVoucher::class, 'vendor_bill_id');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'partial']);
    }

    public function applyPayment(float $amount): void
    {
        $newBalance = max(0, $this->balance_amount - $amount);
        $this->balance_amount = $newBalance;
        $this->status = $newBalance <= 0 ? 'closed' : 'partial';
        $this->save();
    }
}

