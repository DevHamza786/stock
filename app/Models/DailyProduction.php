<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProduction extends Model
{
    use HasFactory;

    protected $table = 'daily_production';

    protected $fillable = [
        'stock_addition_id',
        'stock_issued_id',
        'machine_name',
        'product',
        'operator_name',
        'total_pieces',
        'total_sqft',
        'condition_status',
        'notes',
        'date'
    ];

    protected $casts = [
        'total_sqft' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Get the stock addition that owns the daily production.
     */
    public function stockAddition(): BelongsTo
    {
        return $this->belongsTo(StockAddition::class);
    }

    /**
     * Get the stock issued that owns the daily production.
     */
    public function stockIssued(): BelongsTo
    {
        return $this->belongsTo(StockIssued::class, 'stock_issued_id', 'id');
    }

    /**
     * Boot method to auto-calculate square footage.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dailyProduction) {
            // Calculate total_sqft if not provided
            if (empty($dailyProduction->total_sqft)) {
                $stockAddition = StockAddition::find($dailyProduction->stock_addition_id);
                if ($stockAddition) {
                    $sqftPerPiece = $stockAddition->total_sqft / $stockAddition->total_pieces;
                    $dailyProduction->total_sqft = $sqftPerPiece * $dailyProduction->total_pieces;
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
     * Scope to filter by machine.
     */
    public function scopeByMachine($query, $machineName)
    {
        return $query->where('machine_name', $machineName);
    }

    /**
     * Scope to filter by operator.
     */
    public function scopeByOperator($query, $operatorName)
    {
        return $query->where('operator_name', $operatorName);
    }

    /**
     * Get production efficiency (pieces per hour).
     */
    public function getEfficiencyAttribute(): float
    {
        // Assuming 8 hours work day
        $hours = 8;
        return $this->total_pieces / $hours;
    }
}
