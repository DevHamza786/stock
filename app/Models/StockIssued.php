<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIssued extends Model
{
    use HasFactory;

    protected $table = 'stock_issued';

    protected $fillable = [
        'stock_addition_id',
        'quantity_issued',
        'sqft_issued',
        'purpose',
        'machine_name',
        'operator_name',
        'notes',
        'date'
    ];

    protected $casts = [
        'sqft_issued' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Get the stock addition that owns the stock issued.
     */
    public function stockAddition(): BelongsTo
    {
        return $this->belongsTo(StockAddition::class);
    }

    /**
     * Get the gate pass records for this stock issued.
     */
    public function gatePasses(): HasMany
    {
        return $this->hasMany(GatePass::class);
    }

    /**
     * Get the gate pass records for this stock issued (alias for consistency).
     */
    public function gatePass(): HasMany
    {
        return $this->hasMany(GatePass::class);
    }

    /**
     * Boot method to update stock addition available quantities.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockIssued) {
            // Calculate sqft_issued if not provided
            if (empty($stockIssued->sqft_issued)) {
                $stockAddition = StockAddition::find($stockIssued->stock_addition_id);
                if ($stockAddition) {
                    $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                    $stockIssued->sqft_issued = $sqftPerPiece * $stockIssued->quantity_issued;
                }
            }
        });

        static::created(function ($stockIssued) {
            // Update available quantities in stock addition
            $stockAddition = $stockIssued->stockAddition;
            $stockAddition->available_pieces -= $stockIssued->quantity_issued;
            $stockAddition->available_sqft -= $stockIssued->sqft_issued;
            $stockAddition->save();
        });

        static::deleted(function ($stockIssued) {
            // Restore available quantities in stock addition
            $stockAddition = $stockIssued->stockAddition;
            $stockAddition->available_pieces += $stockIssued->quantity_issued;
            $stockAddition->available_sqft += $stockIssued->sqft_issued;
            $stockAddition->save();
        });
    }

    /**
     * Get the total gate pass quantity for this stock issued.
     */
    public function getTotalGatePassQuantityAttribute(): int
    {
        return $this->gatePasses()->sum('quantity_issued');
    }

    /**
     * Get the total gate pass square footage for this stock issued.
     */
    public function getTotalGatePassSqftAttribute(): float
    {
        return $this->gatePasses()->sum('sqft_issued');
    }

    /**
     * Check if all stock has been dispatched via gate pass.
     */
    public function isFullyDispatched(): bool
    {
        return $this->total_gate_pass_quantity >= $this->quantity_issued;
    }

    /**
     * Get remaining quantity to be dispatched.
     */
    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity_issued - $this->total_gate_pass_quantity;
    }
}
