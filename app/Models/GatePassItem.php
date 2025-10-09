<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'gate_pass_id',
        'stock_addition_id',
        'quantity_issued',
        'sqft_issued',
        'weight_issued',
        'stone'
    ];

    protected $casts = [
        'sqft_issued' => 'decimal:2',
        'weight_issued' => 'decimal:2',
    ];

    /**
     * Set the sqft_issued attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setSqftIssuedAttribute($value)
    {
        $this->attributes['sqft_issued'] = $this->cleanDecimalValue($value);
    }

    /**
     * Set the weight_issued attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setWeightIssuedAttribute($value)
    {
        $this->attributes['weight_issued'] = $this->cleanDecimalValue($value);
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
     * Get the gate pass that owns the item.
     */
    public function gatePass(): BelongsTo
    {
        return $this->belongsTo(GatePass::class);
    }

    /**
     * Get the stock addition that owns the item.
     */
    public function stockAddition(): BelongsTo
    {
        return $this->belongsTo(StockAddition::class);
    }
}
