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
        'machine_id',
        'operator_id',
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
     * Get the machine that owns the stock issued.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get the operator that owns the stock issued.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
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

            // Log stock issued creation
            \App\Models\StockLog::logActivity(
                'issued',
                "Stock issued - {$stockIssued->quantity_issued} pieces for {$stockIssued->purpose}",
                $stockIssued->stock_addition_id,
                $stockIssued->id,
                null,
                null,
                null,
                [
                    'quantity_issued' => $stockIssued->quantity_issued,
                    'sqft_issued' => $stockIssued->sqft_issued,
                    'purpose' => $stockIssued->purpose
                ],
                $stockIssued->quantity_issued,
                $stockIssued->sqft_issued
            );
        });

        static::updating(function ($stockIssued) {
            // If quantity or stock addition is being changed, adjust available quantities
            if ($stockIssued->isDirty(['quantity_issued', 'sqft_issued', 'stock_addition_id'])) {
                $originalQuantity = $stockIssued->getOriginal('quantity_issued');
                $originalSqft = $stockIssued->getOriginal('sqft_issued');
                $originalStockAdditionId = $stockIssued->getOriginal('stock_addition_id');
                $newQuantity = $stockIssued->quantity_issued;
                $newSqft = $stockIssued->sqft_issued;
                $newStockAdditionId = $stockIssued->stock_addition_id;

                // If stock addition changed, restore to old stock addition first
                if ($originalStockAdditionId != $newStockAdditionId) {
                    $oldStockAddition = StockAddition::find($originalStockAdditionId);
                    if ($oldStockAddition) {
                        $oldStockAddition->available_pieces += $originalQuantity;
                        $oldStockAddition->available_sqft += $originalSqft;
                        $oldStockAddition->save();
                    }

                    // Then subtract from new stock addition
                    $newStockAddition = $stockIssued->stockAddition;
                    $newStockAddition->available_pieces -= $newQuantity;
                    $newStockAddition->available_sqft -= $newSqft;
                    $newStockAddition->save();
                } else {
                    // Same stock addition, just adjust the difference
                    $stockAddition = $stockIssued->stockAddition;
                    $quantityDiff = $newQuantity - $originalQuantity;
                    $sqftDiff = $newSqft - $originalSqft;

                    $stockAddition->available_pieces -= $quantityDiff;
                    $stockAddition->available_sqft -= $sqftDiff;
                    $stockAddition->save();
                }
            }
        });

        static::updated(function ($stockIssued) {
            // Log stock issued updates
            if ($stockIssued->wasChanged(['quantity_issued', 'sqft_issued', 'stock_addition_id'])) {
                $originalQuantity = $stockIssued->getOriginal('quantity_issued');
                $newQuantity = $stockIssued->quantity_issued;

                \App\Models\StockLog::logActivity(
                    'updated',
                    "Stock issued updated - quantity changed from {$originalQuantity} to {$newQuantity} pieces",
                    $stockIssued->stock_addition_id,
                    $stockIssued->id,
                    null,
                    null,
                    [
                        'quantity_issued' => $originalQuantity,
                        'sqft_issued' => $stockIssued->getOriginal('sqft_issued')
                    ],
                    [
                        'quantity_issued' => $newQuantity,
                        'sqft_issued' => $stockIssued->sqft_issued
                    ],
                    $newQuantity - $originalQuantity,
                    $stockIssued->sqft_issued - $stockIssued->getOriginal('sqft_issued')
                );
            }
        });

        static::deleting(function ($stockIssued) {
            // Store gate pass ID before deletion
            $stockIssued->gate_pass_id_for_log = $stockIssued->gatePasses()->first()?->id;
        });

        static::deleted(function ($stockIssued) {
            // Check if this deletion is coming from a gatepass deletion
            // If so, stock restoration is already handled in the GatePass model
            $isGatePassDeletion = request()->route() &&
                str_contains(request()->route()->getName(), 'gate-pass') &&
                request()->isMethod('DELETE');

            if (!$isGatePassDeletion) {
                // Only restore stock if this is not a gatepass deletion
                $stockAddition = $stockIssued->stockAddition;
                if ($stockAddition) {
                    $stockAddition->available_pieces += $stockIssued->quantity_issued;
                    $stockAddition->available_sqft += $stockIssued->sqft_issued;
                    $stockAddition->save();
                }
            }

            // Get the ID before deletion for logging
            $stockIssuedId = $stockIssued->id;
            $stockAdditionId = $stockIssued->stock_addition_id;
            $quantityIssued = $stockIssued->quantity_issued;
            $sqftIssued = $stockIssued->sqft_issued;
            $gatePassId = $stockIssued->gate_pass_id_for_log ?? null;

            // Log stock issued deletion (don't reference the deleted record to avoid FK constraint)
            \App\Models\StockLog::logActivity(
                'deleted',
                "Stock issued deleted (ID: {$stockIssuedId}) - {$quantityIssued} pieces restored",
                $stockAdditionId,
                null, // Set to null to avoid foreign key constraint violation
                $gatePassId,
                null,
                [
                    'quantity_issued' => $quantityIssued,
                    'sqft_issued' => $sqftIssued,
                    'deleted_stock_issued_id' => $stockIssuedId // Store ID in old_values for reference
                ],
                null,
                $quantityIssued,
                $sqftIssued,
                0
            );
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
