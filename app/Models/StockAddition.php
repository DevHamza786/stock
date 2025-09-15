<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAddition extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'mine_vendor_id',
        'stone',
        'size_3d',
        'total_pieces',
        'total_sqft',
        'condition_status',
        'available_pieces',
        'available_sqft',
        'date'
    ];

    protected $casts = [
        'total_sqft' => 'decimal:2',
        'available_sqft' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Get the product that owns the stock addition.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the mine vendor that owns the stock addition.
     */
    public function mineVendor(): BelongsTo
    {
        return $this->belongsTo(MineVendor::class);
    }

    /**
     * Get the stock issued records for this stock addition.
     */
    public function stockIssued(): HasMany
    {
        return $this->hasMany(StockIssued::class);
    }

    /**
     * Get the daily production records for this stock addition.
     */
    public function dailyProduction(): HasMany
    {
        return $this->hasMany(DailyProduction::class);
    }

    /**
     * Calculate square footage from 3D dimensions.
     * Format: 20143 (20x14x3)
     */
    public static function calculateSqftFromSize3d(string $size3d): float
    {
        if (strlen($size3d) < 3) {
            return 0;
        }

        // Extract dimensions from size_3d (e.g., 20143 = 20x14x3)
        $length = (int) substr($size3d, 0, 2);
        $width = (int) substr($size3d, 2, 2);
        $height = (int) substr($size3d, 4, 1);

        // Calculate square footage (length * width)
        return $length * $width;
    }

    /**
     * Calculate total square footage for multiple pieces.
     */
    public static function calculateTotalSqft(string $size3d, int $pieces): float
    {
        $sqftPerPiece = self::calculateSqftFromSize3d($size3d);
        return $sqftPerPiece * $pieces;
    }

    /**
     * Boot method to auto-calculate square footage.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockAddition) {
            if (empty($stockAddition->total_sqft)) {
                $stockAddition->total_sqft = self::calculateTotalSqft(
                    $stockAddition->size_3d,
                    $stockAddition->total_pieces
                );
            }

            // Set available quantities equal to total quantities initially
            $stockAddition->available_pieces = $stockAddition->total_pieces;
            $stockAddition->available_sqft = $stockAddition->total_sqft;
        });

        static::updating(function ($stockAddition) {
            if ($stockAddition->isDirty(['size_3d', 'total_pieces'])) {
                $stockAddition->total_sqft = self::calculateTotalSqft(
                    $stockAddition->size_3d,
                    $stockAddition->total_pieces
                );
            }
        });
    }

    /**
     * Get the total issued pieces for this stock addition.
     */
    public function getTotalIssuedPiecesAttribute(): int
    {
        return $this->stockIssued()->sum('quantity_issued');
    }

    /**
     * Get the total issued square footage for this stock addition.
     */
    public function getTotalIssuedSqftAttribute(): float
    {
        return $this->stockIssued()->sum('sqft_issued');
    }

    /**
     * Check if there's available stock.
     */
    public function hasAvailableStock(): bool
    {
        return $this->available_pieces > 0;
    }

    /**
     * Get available stock percentage.
     */
    public function getAvailableStockPercentageAttribute(): float
    {
        if ($this->total_pieces == 0) {
            return 0;
        }

        return ($this->available_pieces / $this->total_pieces) * 100;
    }
}
