<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'can_add_stock',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'can_add_stock' => 'boolean',
    ];

    /**
     * Scope for active machines
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope for machines that can add stock
     */
    public function scopeCanAddStock($query)
    {
        return $query->where('can_add_stock', true);
    }

    /**
     * Get the user who created the machine
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the machine
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the stock issued records for this machine
     */
    public function stockIssued(): HasMany
    {
        return $this->hasMany(StockIssued::class);
    }

    /**
     * Check if machine is being used in stock_issued table
     */
    public function isBeingUsed(): bool
    {
        return $this->stockIssued()->count() > 0;
    }
}
