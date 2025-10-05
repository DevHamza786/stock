<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_id',
        'phone',
        'email',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope for active operators
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Get the user who created the operator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the operator
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the stock issued records for this operator
     */
    public function stockIssued(): HasMany
    {
        return $this->hasMany(StockIssued::class);
    }

    /**
     * Check if operator is being used in stock_issued table
     */
    public function isBeingUsed(): bool
    {
        return $this->stockIssued()->count() > 0;
    }
}
