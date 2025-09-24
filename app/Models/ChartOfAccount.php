<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'description',
        'account_type',
        'account_subtype',
        'normal_balance',
        'parent_account_id',
        'level',
        'is_active',
        'is_system_account',
        'opening_balance',
        'current_balance'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_account' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'level' => 'integer'
    ];

    /**
     * Get the parent account.
     */
    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Get the child accounts.
     */
    public function childAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_account_id');
    }

    /**
     * Get the account transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class, 'account_id');
    }

    /**
     * Scope to get accounts by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope to get active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get main accounts (level 1).
     */
    public function scopeMainAccounts($query)
    {
        return $query->where('level', 1);
    }

    /**
     * Scope to get sub accounts.
     */
    public function scopeSubAccounts($query)
    {
        return $query->where('level', '>', 1);
    }

    /**
     * Get the account hierarchy path.
     */
    public function getAccountPathAttribute(): string
    {
        $path = $this->account_name;
        $parent = $this->parentAccount;

        while ($parent) {
            $path = $parent->account_name . ' > ' . $path;
            $parent = $parent->parentAccount;
        }

        return $path;
    }

    /**
     * Get the account balance based on normal balance.
     */
    public function getBalanceAttribute(): float
    {
        $debitTotal = $this->transactions()->sum('debit_amount');
        $creditTotal = $this->transactions()->sum('credit_amount');

        if ($this->normal_balance === 'DEBIT') {
            return $this->opening_balance + $debitTotal - $creditTotal;
        } else {
            return $this->opening_balance + $creditTotal - $debitTotal;
        }
    }

    /**
     * Update current balance.
     */
    public function updateBalance(): void
    {
        $this->current_balance = $this->balance;
        $this->save();
    }

    /**
     * Get all descendant accounts recursively.
     */
    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->childAccounts as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Get account type options.
     */
    public static function getAccountTypes(): array
    {
        return [
            'ASSET' => 'Assets',
            'LIABILITY' => 'Liabilities',
            'EQUITY' => 'Owner\'s Equity',
            'REVENUE' => 'Revenue',
            'EXPENSE' => 'Expenses'
        ];
    }

    /**
     * Get account subtype options.
     */
    public static function getAccountSubtypes(): array
    {
        return [
            'CURRENT_ASSET' => 'Current Asset',
            'FIXED_ASSET' => 'Fixed Asset',
            'INVENTORY' => 'Inventory',
            'ACCOUNTS_RECEIVABLE' => 'Accounts Receivable',
            'CASH' => 'Cash',
            'CURRENT_LIABILITY' => 'Current Liability',
            'LONG_TERM_LIABILITY' => 'Long Term Liability',
            'ACCOUNTS_PAYABLE' => 'Accounts Payable',
            'OWNER_EQUITY' => 'Owner Equity',
            'RETAINED_EARNINGS' => 'Retained Earnings',
            'CAPITAL' => 'Capital',
            'SALES_REVENUE' => 'Sales Revenue',
            'SERVICE_REVENUE' => 'Service Revenue',
            'OTHER_INCOME' => 'Other Income',
            'COST_OF_GOODS_SOLD' => 'Cost of Goods Sold',
            'OPERATING_EXPENSE' => 'Operating Expense',
            'ADMINISTRATIVE_EXPENSE' => 'Administrative Expense',
            'OTHER_EXPENSE' => 'Other Expense'
        ];
    }
}
