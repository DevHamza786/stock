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
