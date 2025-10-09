<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GatePass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gate_pass';

    protected $fillable = [
        'stock_issued_id',
        'destination',
        'vehicle_number',
        'driver_name',
        'client_name',
        'client_number',
        'status',
        'notes',
        'date'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Get the total quantity issued across all items.
     */
    public function getQuantityIssuedAttribute(): int
    {
        return $this->items()->sum('quantity_issued');
    }

    /**
     * Get the total square feet issued across all items.
     */
    public function getSqftIssuedAttribute(): float
    {
        return $this->items()->sum('sqft_issued') ?? 0;
    }

    /**
     * Get the total weight issued across all items.
     */
    public function getWeightIssuedAttribute(): float
    {
        return $this->items()->sum('weight_issued') ?? 0;
    }

    /**
     * Get the stock issued that owns the gate pass.
     */
    public function stockIssued(): BelongsTo
    {
        return $this->belongsTo(StockIssued::class);
    }

    /**
     * Boot method to auto-calculate square footage and handle stock restoration.
     */
    protected static function boot()
    {
        parent::boot();

        // No longer need creating event since we calculate totals from items

        static::deleting(function ($gatePass) {
            // Handle old single-item gate passes (with stockIssued relationship)
            $stockIssued = $gatePass->stockIssued;
            if ($stockIssued && $stockIssued->stockAddition) {
                $stockAddition = $stockIssued->stockAddition;

                // Store current values for logging
                $oldAvailablePieces = $stockAddition->available_pieces;
                $oldAvailableSqft = $stockAddition->available_sqft;

                // Restore stock quantities
                $stockAddition->available_pieces += $stockIssued->quantity_issued;
                $stockAddition->available_sqft += $stockIssued->sqft_issued;
                $stockAddition->save();

                // Log the stock restoration activity
                \App\Models\StockLog::logActivity(
                    'restored',
                    "Gate pass deleted - {$stockIssued->quantity_issued} pieces restored to stock",
                    $stockAddition->id,
                    $stockIssued->id,
                    $gatePass->id,
                    null,
                    ['available_pieces' => $oldAvailablePieces, 'available_sqft' => $oldAvailableSqft],
                    ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                    $stockIssued->quantity_issued,
                    $stockIssued->sqft_issued
                );
            }

            // Handle new multi-item gate passes (with items relationship)
            $items = $gatePass->items()->with('stockAddition')->get();
            foreach ($items as $item) {
                if ($item->stockAddition) {
                    $stockAddition = $item->stockAddition;

                    // Store current values for logging
                    $oldAvailablePieces = $stockAddition->available_pieces;
                    $oldAvailableSqft = $stockAddition->available_sqft;

                    // Restore stock quantities
                    $stockAddition->available_pieces += $item->quantity_issued;
                    if ($item->sqft_issued) {
                        $stockAddition->available_sqft += $item->sqft_issued;
                    }
                    $stockAddition->save();

                    // Log the stock restoration activity
                    \App\Models\StockLog::logActivity(
                        'restored',
                        "Gate pass deleted - {$item->quantity_issued} pieces restored to stock",
                        $stockAddition->id,
                        null, // No stock_issued_id for multi-item gate passes
                        $gatePass->id,
                        null,
                        ['available_pieces' => $oldAvailablePieces, 'available_sqft' => $oldAvailableSqft],
                        ['available_pieces' => $stockAddition->available_pieces, 'available_sqft' => $stockAddition->available_sqft],
                        $item->quantity_issued,
                        $item->sqft_issued ?? 0
                    );
                }
            }

            // Delete associated StockIssued records for multi-item gate passes
            if ($items->isNotEmpty()) {
                foreach ($items as $item) {
                    $stockIssued = \App\Models\StockIssued::where('stock_addition_id', $item->stock_addition_id)
                        ->where('purpose', 'Gate Pass Dispatch')
                        ->where('notes', 'like', '%Auto-created for gate pass dispatch%')
                        ->first();

                    if ($stockIssued) {
                        $stockIssued->deleteQuietly();
                    }
                }
            }
        });
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by destination.
     */
    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', $destination);
    }

    /**
     * Scope to filter by vehicle number.
     */
    public function scopeByVehicle($query, $vehicleNumber)
    {
        return $query->where('vehicle_number', $vehicleNumber);
    }

    /**
     * Get the gate pass number.
     */
    public function getGatePassNumberAttribute(): string
    {
        return 'GP-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get related stock addition through stock issued.
     */
    public function stockAddition()
    {
        return $this->hasOneThrough(
            StockAddition::class,
            StockIssued::class,
            'id',
            'id',
            'stock_issued_id',
            'stock_addition_id'
        );
    }

    /**
     * Get the gate pass items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(GatePassItem::class);
    }
}
