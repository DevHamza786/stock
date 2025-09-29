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
        'length',
        'height',
        'diameter',
        'weight',
        'size_3d', // Keep for backward compatibility
        'total_pieces',
        'total_sqft',
        'condition_status',
        'available_pieces',
        'available_sqft',
        'available_weight',
        'date'
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'total_sqft' => 'decimal:2',
        'available_sqft' => 'decimal:2',
        'available_weight' => 'decimal:2',
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
     * Calculate square footage from 2D dimensions (cm to sqft).
     * Conversion factor: 1 cm² = 0.00107639 sqft
     */
    public static function calculateSqftFromDimensions(float $length, float $height): float
    {
        if ($length <= 0 || $height <= 0) {
            return 0;
        }

        // Calculate area in cm²
        $areaCm = $length * $height;
        
        // Convert to sqft (1 cm² = 0.00107639 sqft)
        return $areaCm * 0.00107639;
    }

    /**
     * Calculate square footage from 3D dimensions (legacy method).
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

        // Calculate square footage (length * width)
        return $length * $width;
    }

    /**
     * Calculate total square footage for multiple pieces using new 2D method.
     */
    public static function calculateTotalSqftFromDimensions(float $length, float $height, int $pieces): float
    {
        $sqftPerPiece = self::calculateSqftFromDimensions($length, $height);
        return $sqftPerPiece * $pieces;
    }

    /**
     * Calculate total square footage for multiple pieces (legacy method).
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
                // Use new 2D method if length and height are provided
                if (!empty($stockAddition->length) && !empty($stockAddition->height)) {
                    $stockAddition->total_sqft = self::calculateTotalSqftFromDimensions(
                        $stockAddition->length,
                        $stockAddition->height,
                        $stockAddition->total_pieces
                    );
                } 
                // Fallback to legacy 3D method
                elseif (!empty($stockAddition->size_3d)) {
                    $stockAddition->total_sqft = self::calculateTotalSqft(
                        $stockAddition->size_3d,
                        $stockAddition->total_pieces
                    );
                }
            }

            // Set available quantities equal to total quantities initially
            $stockAddition->available_pieces = $stockAddition->total_pieces;
            $stockAddition->available_sqft = $stockAddition->total_sqft;
        });

        static::updating(function ($stockAddition) {
            // Check if there are any stock issues for this stock addition
            if ($stockAddition->stockIssued()->count() > 0) {
                // Check if critical fields are being changed
                $criticalFields = ['length', 'height', 'size_3d', 'total_pieces', 'total_sqft'];
                $hasCriticalChanges = false;
                
                foreach ($criticalFields as $field) {
                    if ($stockAddition->isDirty($field)) {
                        $hasCriticalChanges = true;
                        break;
                    }
                }
                
                if ($hasCriticalChanges) {
                    throw new \Exception('Cannot update stock dimensions or quantities after stock has been issued. Please delete all related stock issuances first.');
                }
            }
            
            // Recalculate if any dimension fields change
            if ($stockAddition->isDirty(['length', 'height', 'size_3d', 'total_pieces'])) {
                // Use new 2D method if length and height are provided
                if (!empty($stockAddition->length) && !empty($stockAddition->height)) {
                    $stockAddition->total_sqft = self::calculateTotalSqftFromDimensions(
                        $stockAddition->length,
                        $stockAddition->height,
                        $stockAddition->total_pieces
                    );
                } 
                // Fallback to legacy 3D method
                elseif (!empty($stockAddition->size_3d)) {
                    $stockAddition->total_sqft = self::calculateTotalSqft(
                        $stockAddition->size_3d,
                        $stockAddition->total_pieces
                    );
                }
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
     * Check if this stock has been issued (has stock issuances).
     */
    public function hasBeenIssued(): bool
    {
        return $this->stockIssued()->count() > 0;
    }

    /**
     * Check if this stock can be updated (no stock issuances exist).
     */
    public function canBeUpdated(): bool
    {
        return !$this->hasBeenIssued();
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
