<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'entry_type',
        'total_debit',
        'total_credit',
        'status',
        'created_by',
        'approved_by',
        'posted_at',
        'notes'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime'
    ];

    /**
     * Get the user who created the journal entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the journal entry.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the transactions for this journal entry.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    /**
     * Boot method to generate entry number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journalEntry) {
            if (empty($journalEntry->entry_number)) {
                $journalEntry->entry_number = self::generateEntryNumber();
            }
        });
    }

    /**
     * Generate unique entry number.
     */
    public static function generateEntryNumber(): string
    {
        $year = date('Y');
        $prefix = "JE-{$year}-";

        $lastEntry = self::where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by entry type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('entry_type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Check if the journal entry is balanced.
     */
    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    /**
     * Post the journal entry.
     */
    public function post(): bool
    {
        if (!$this->isBalanced()) {
            return false;
        }

        $this->status = 'POSTED';
        $this->posted_at = now();
        $this->save();

        // Update account balances
        foreach ($this->transactions as $transaction) {
            $account = $transaction->account;
            $account->updateBalance();
        }

        return true;
    }

    /**
     * Reverse the journal entry.
     */
    public function reverse(): bool
    {
        if ($this->status !== 'POSTED') {
            return false;
        }

        // Create reversing entry
        $reversingEntry = self::create([
            'entry_date' => now()->toDateString(),
            'description' => "Reversal of {$this->entry_number}",
            'entry_type' => 'ADJUSTMENT',
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
            'notes' => "Reversing entry for {$this->entry_number}"
        ]);

        // Create reversing transactions
        foreach ($this->transactions as $transaction) {
            AccountTransaction::create([
                'journal_entry_id' => $reversingEntry->id,
                'account_id' => $transaction->account_id,
                'debit_amount' => $transaction->credit_amount,
                'credit_amount' => $transaction->debit_amount,
                'description' => "Reversal: {$transaction->description}",
                'reference_type' => $transaction->reference_type,
                'reference_id' => $transaction->reference_id
            ]);
        }

        $this->status = 'REVERSED';
        $this->save();

        return true;
    }

    /**
     * Get entry type options.
     */
    public static function getEntryTypes(): array
    {
        return [
            'MANUAL' => 'Manual Entry',
            'AUTO_STOCK_ADD' => 'Stock Addition',
            'AUTO_STOCK_ISSUE' => 'Stock Issued',
            'AUTO_PRODUCTION' => 'Daily Production',
            'AUTO_GATE_PASS' => 'Gate Pass',
            'AUTO_SALE' => 'Sales',
            'AUTO_PURCHASE' => 'Purchase',
            'ADJUSTMENT' => 'Adjustment'
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatuses(): array
    {
        return [
            'DRAFT' => 'Draft',
            'POSTED' => 'Posted',
            'REVERSED' => 'Reversed'
        ];
    }
}
