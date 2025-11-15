<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'description',
        'reference_type',
        'reference_id'
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'reference_id' => 'integer'
    ];

    /**
     * Get the journal entry that owns the transaction.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account that owns the transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    /**
     * Get the reference model (polymorphic relationship).
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = match($this->reference_type) {
            'stock_addition' => StockAddition::class,
            'stock_issued' => StockIssued::class,
            'daily_production' => DailyProduction::class,
            'gate_pass' => GatePass::class,
            'product' => Product::class,
            'mine_vendor' => MineVendor::class,
            'bank_payment_voucher' => BankPaymentVoucher::class,
            'cash_payment_voucher' => CashPaymentVoucher::class,
            'purchase_voucher' => PurchaseVoucher::class,
            default => null
        };

        return $modelClass ? $modelClass::find($this->reference_id) : null;
    }

    /**
     * Scope to filter by account.
     */
    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
            $q->whereBetween('entry_date', [$startDate, $endDate]);
        });
    }

    /**
     * Scope to filter by reference.
     */
    public function scopeByReference($query, $type, $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    /**
     * Get the transaction amount (positive for debit, negative for credit).
     */
    public function getAmountAttribute(): float
    {
        return $this->debit_amount - $this->credit_amount;
    }

    /**
     * Get the transaction type.
     */
    public function getTransactionTypeAttribute(): string
    {
        return $this->debit_amount > 0 ? 'DEBIT' : 'CREDIT';
    }
}
