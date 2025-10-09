<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProductionItem extends Model
{
    use HasFactory;

    protected $table = 'daily_production_items';

    protected $fillable = [
        'daily_production_id',
        'product_name',
        'size',
        'diameter',
        'condition_status',
        'special_status',
        'total_pieces',
        'total_sqft',
        'total_weight',
        'narration'
    ];

    protected $casts = [
        'total_sqft' => 'decimal:2',
        'total_weight' => 'decimal:2',
    ];

    /**
     * Set the total_sqft attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setTotalSqftAttribute($value)
    {
        $this->attributes['total_sqft'] = $this->cleanDecimalValue($value);
    }

    /**
     * Set the total_weight attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setTotalWeightAttribute($value)
    {
        $this->attributes['total_weight'] = $this->cleanDecimalValue($value);
    }

    /**
     * Clean a decimal value by removing non-numeric characters (except decimal point)
     */
    private function cleanDecimalValue($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }
        
        // If already numeric, return as is
        if (is_numeric($value)) {
            return $value;
        }
        
        // Remove all non-numeric characters except decimal point
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        
        // Check if we have a valid number after cleaning
        if ($cleaned !== '' && is_numeric($cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

    /**
     * Get the daily production that owns the item.
     */
    public function dailyProduction(): BelongsTo
    {
        return $this->belongsTo(DailyProduction::class);
    }

    /**
     * Scope to filter by product name.
     */
    public function scopeByProduct($query, $productName)
    {
        return $query->where('product_name', $productName);
    }

    /**
     * Scope to filter by condition status.
     */
    public function scopeByCondition($query, $conditionStatus)
    {
        return $query->where('condition_status', $conditionStatus);
    }

    /**
     * Get the product identifier for matching.
     */
    public function getProductIdentifierAttribute(): string
    {
        return strtolower(trim($this->product_name . '|' . $this->size . '|' . $this->diameter . '|' . $this->condition_status . '|' . $this->special_status));
    }
}
