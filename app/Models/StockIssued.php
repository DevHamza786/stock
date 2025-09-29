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
        'weight_issued',
        'purpose',
        'machine_name',
        'operator_name',
        'notes',
        'stone',
        'date'
    ];

    protected $casts = [
        'sqft_issued' => 'decimal:2',
        'weight_issued' => 'decimal:2',
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
     * Get the daily production records for this stock issued.
     */
    public function dailyProduction(): HasMany
    {
        return $this->hasMany(DailyProduction::class);
    }

    /**
     * Boot method to update stock addition available quantities.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockIssued) {
            // Calculate sqft_issued if not provided
            // Auto-fill stone field from stock addition
            if (empty($stockIssued->sqft_issued) || empty($stockIssued->stone)) {
                $stockAddition = StockAddition::find($stockIssued->stock_addition_id);
                if ($stockAddition) {
                    if (empty($stockIssued->sqft_issued)) {
                        $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                        $stockIssued->sqft_issued = $sqftPerPiece * $stockIssued->quantity_issued;
                    }
                    if (empty($stockIssued->stone)) {
                        $stockIssued->stone = $stockAddition->stone;
                    }
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

        static::updating(function ($stockIssued) {
            // If quantity is being changed, adjust available quantities
            if ($stockIssued->isDirty(['quantity_issued', 'sqft_issued'])) {
                $originalQuantity = $stockIssued->getOriginal('quantity_issued');
                $originalSqft = $stockIssued->getOriginal('sqft_issued');
                $newQuantity = $stockIssued->quantity_issued;
                $newSqft = $stockIssued->sqft_issued;
                
                $stockAddition = $stockIssued->stockAddition;
                
                // Restore the original quantities first
                $stockAddition->available_pieces += $originalQuantity;
                $stockAddition->available_sqft += $originalSqft;
                
                // Then subtract the new quantities
                $stockAddition->available_pieces -= $newQuantity;
                $stockAddition->available_sqft -= $newSqft;
                
                $stockAddition->save();
            }
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
