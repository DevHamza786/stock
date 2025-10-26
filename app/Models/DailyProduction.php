<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyProduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'daily_production';

    protected $fillable = [
        'stock_addition_id',
        'stock_issued_id',
        'machine_name',
        'operator_name',
        'notes',
        'stone',
        'date',
        'status',
        'wastage_sqft'
    ];

    protected $casts = [
        'date' => 'datetime',
        'wastage_sqft' => 'decimal:2',
    ];

    /**
     * Set the wastage_sqft attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setWastageSqftAttribute($value)
    {
        $this->attributes['wastage_sqft'] = $this->cleanDecimalValue($value);
    }

    /**
     * Clean a decimal value by removing non-numeric characters (except decimal point)
     */
    private function cleanDecimalValue($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        
        if ($cleaned !== '' && is_numeric($cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

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
     * Get the production items for this daily production.
     */
    public function items(): HasMany
    {
        return $this->hasMany(DailyProductionItem::class);
    }

    /**
     * Get the machine that owns the daily production.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_name', 'name');
    }

    /**
     * Get the operator that owns the daily production.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_name', 'name');
    }

    /**
     * Get the condition status that owns the daily production.
     */
    public function conditionStatus(): BelongsTo
    {
        return $this->belongsTo(ConditionStatus::class, 'condition_status', 'name');
    }

    /**
     * Check if the production is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if the production is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Close the production.
     */
    public function close(): bool
    {
        return $this->update(['status' => 'closed']);
    }

    /**
     * Open the production.
     */
    public function open(): bool
    {
        return $this->update(['status' => 'open']);
    }

    /**
     * Get the stock additions that were created from this daily production.
     */
    public function producedStockAdditions()
    {
        // Get production item names and conditions
        $productionItemNames = $this->items()->pluck('product_name')->toArray();
        $productionItemConditions = $this->items()->pluck('condition_status')->toArray();
        
        // Find stock additions created on the same date with matching product names and conditions
        return StockAddition::where('date', $this->date->format('Y-m-d'))
            ->whereIn('stone', $productionItemNames)
            ->whereIn('condition_status', $productionItemConditions)
            ->where('pid', 'like', 'STK-%') // Only STK- format PIDs
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get total pieces from all items.
     */
    public function getTotalPiecesAttribute(): int
    {
        return $this->items()->sum('total_pieces');
    }

    /**
     * Get total square feet from all items.
     */
    public function getTotalSqftAttribute(): float
    {
        return $this->items()->sum('total_sqft');
    }

    /**
     * Get total weight from all items.
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->items()->sum('total_weight');
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

    /**
     * Check if a product with same specifications already exists in this production.
     */
    public function hasProductWithSameSpecs(string $productName, string $size = null, string $diameter = null, string $conditionStatus, string $specialStatus = null): bool
    {
        return $this->items()
            ->where('product_name', $productName)
            ->where('size', $size)
            ->where('diameter', $diameter)
            ->where('condition_status', $conditionStatus)
            ->where('special_status', $specialStatus)
            ->exists();
    }

    /**
     * Get existing item with same specifications.
     */
    public function getItemWithSameSpecs(string $productName, string $size = null, string $diameter = null, string $conditionStatus, string $specialStatus = null): ?DailyProductionItem
    {
        return $this->items()
            ->where('product_name', $productName)
            ->where('size', $size)
            ->where('diameter', $diameter)
            ->where('condition_status', $conditionStatus)
            ->where('special_status', $specialStatus)
            ->first();
    }
}
