<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the stock additions for the product.
     */
    public function stockAdditions()
    {
        return $this->hasMany(StockAddition::class);
    }

    /**
     * Get the total available stock for this product.
     */
    public function getTotalAvailableStockAttribute()
    {
        return $this->stockAdditions()
            ->where('available_pieces', '>', 0)
            ->sum('available_pieces');
    }

    /**
     * Get the total available square footage for this product.
     */
    public function getTotalAvailableSqftAttribute()
    {
        return $this->stockAdditions()
            ->where('available_sqft', '>', 0)
            ->sum('available_sqft');
    }
}
