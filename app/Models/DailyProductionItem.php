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
        'narration'
    ];

    protected $casts = [
        'total_sqft' => 'decimal:2',
    ];

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
