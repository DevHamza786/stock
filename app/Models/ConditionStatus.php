<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConditionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the stock additions for this condition status.
     */
    public function stockAdditions(): HasMany
    {
        return $this->hasMany(StockAddition::class, 'condition_status', 'name');
    }

    /**
     * Get the daily productions for this condition status.
     */
    public function dailyProductions(): HasMany
    {
        return $this->hasMany(DailyProduction::class, 'condition_status', 'name');
    }

    /**
     * Scope to get only active condition statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the color style for UI.
     */
    public function getColorStyleAttribute(): string
    {
        return "background-color: {$this->color}; color: white;";
    }

    /**
     * Get the badge class for UI.
     */
    public function getBadgeClassAttribute(): string
    {
        $colorMap = [
            '#3B82F6' => 'bg-blue-100 text-blue-800',
            '#10B981' => 'bg-green-100 text-green-800',
            '#F59E0B' => 'bg-yellow-100 text-yellow-800',
            '#EF4444' => 'bg-red-100 text-red-800',
            '#8B5CF6' => 'bg-purple-100 text-purple-800',
            '#06B6D4' => 'bg-cyan-100 text-cyan-800',
            '#F97316' => 'bg-orange-100 text-orange-800',
            '#84CC16' => 'bg-lime-100 text-lime-800',
        ];

        return $colorMap[$this->color] ?? 'bg-gray-100 text-gray-800';
    }
}
