<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GatePass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gate_pass';

    protected $fillable = [
        'stock_issued_id',
        'quantity_issued',
        'sqft_issued',
        'destination',
        'vehicle_number',
        'driver_name',
        'status',
        'notes',
        'date'
    ];

    protected $casts = [
        'sqft_issued' => 'decimal:2',
        'date' => 'datetime',
    ];

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

        static::creating(function ($gatePass) {
            // Calculate sqft_issued if not provided
            if (empty($gatePass->sqft_issued)) {
                $stockIssued = StockIssued::find($gatePass->stock_issued_id);
                if ($stockIssued) {
                    $sqftPerPiece = $stockIssued->sqft_issued / $stockIssued->quantity_issued;
                    $gatePass->sqft_issued = $sqftPerPiece * $gatePass->quantity_issued;
                }
            }
        });

        static::deleting(function ($gatePass) {
            // Store the stock issued data before deletion for stock restoration
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
}
