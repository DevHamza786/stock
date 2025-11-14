<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MineVendor extends Model
{
    use HasFactory;

    protected $table = 'mine_vendors';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
        'chart_of_account_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the stock additions from this vendor.
     */
    public function stockAdditions()
    {
        return $this->hasMany(StockAddition::class);
    }

    /**
     * Get the total stock purchased from this vendor.
     */
    public function getTotalStockPurchasedAttribute()
    {
        return $this->stockAdditions()->sum('total_pieces');
    }

    /**
     * Get the total square footage purchased from this vendor.
     */
    public function getTotalSqftPurchasedAttribute()
    {
        return $this->stockAdditions()->sum('total_sqft');
    }

    /**
     * Get the chart of account (accounts payable) for this vendor.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(\App\Models\ChartOfAccount::class, 'chart_of_account_id');
    }
}
