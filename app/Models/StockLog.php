<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stock_addition_id',
        'stock_issued_id',
        'gate_pass_id',
        'daily_production_id',
        'action_type',
        'description',
        'old_values',
        'new_values',
        'quantity_changed',
        'sqft_changed',
        'weight_changed',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'sqft_changed' => 'decimal:2',
        'weight_changed' => 'decimal:2',
    ];

    /**
     * Get the stock addition that owns the stock log.
     */
    public function stockAddition(): BelongsTo
    {
        return $this->belongsTo(StockAddition::class);
    }

    /**
     * Get the stock issued that owns the stock log.
     */
    public function stockIssued(): BelongsTo
    {
        return $this->belongsTo(StockIssued::class);
    }

    /**
     * Get the gate pass that owns the stock log.
     */
    public function gatePass(): BelongsTo
    {
        return $this->belongsTo(GatePass::class);
    }

    /**
     * Get the daily production that owns the stock log.
     */
    public function dailyProduction(): BelongsTo
    {
        return $this->belongsTo(DailyProduction::class);
    }

    /**
     * Get the user that created the stock log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a stock log entry.
     */
    public static function logActivity(
        string $actionType,
        string $description,
        int $stockAdditionId,
        ?int $stockIssuedId = null,
        ?int $gatePassId = null,
        ?int $dailyProductionId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        int $quantityChanged = 0,
        float $sqftChanged = 0,
        float $weightChanged = 0
    ): self {
        return self::create([
            'action_type' => $actionType,
            'description' => $description,
            'stock_addition_id' => $stockAdditionId,
            'stock_issued_id' => $stockIssuedId,
            'gate_pass_id' => $gatePassId,
            'daily_production_id' => $dailyProductionId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'quantity_changed' => $quantityChanged,
            'sqft_changed' => $sqftChanged,
            'weight_changed' => $weightChanged,
            'user_id' => auth()->id(),
        ]);
    }
}
