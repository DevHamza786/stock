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
        'pid',
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
        'diameter' => 'decimal:2',
        'weight' => 'decimal:2',
        'total_sqft' => 'decimal:2',
        'available_sqft' => 'decimal:2',
        'available_weight' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Set the length attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setLengthAttribute($value)
    {
        $this->attributes['length'] = $this->cleanDecimalValue($value);
    }

    /**
     * Set the height attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setHeightAttribute($value)
    {
        $this->attributes['height'] = $this->cleanDecimalValue($value);
    }

    /**
     * Set the diameter attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setDiameterAttribute($value)
    {
        $this->attributes['diameter'] = $this->cleanDecimalValue($value);
    }

    /**
     * Set the weight attribute - convert empty strings to null and strip non-numeric characters
     */
    protected function setWeightAttribute($value)
    {
        $this->attributes['weight'] = $this->cleanDecimalValue($value);
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
     * Get the stock logs for this stock addition.
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Recalculate available pieces based on issued stock.
     */
    public function recalculateAvailablePieces(): void
    {
        $totalIssued = $this->stockIssued()->sum('quantity_issued');
        $totalIssuedSqft = $this->stockIssued()->sum('sqft_issued');
        
        $this->available_pieces = max(0, $this->total_pieces - $totalIssued);
        $this->available_sqft = max(0, $this->total_sqft - $totalIssuedSqft);
        $this->save();
    }

    /**
     * Get the total issued pieces for this stock addition.
     */
    public function getTotalIssuedPieces(): int
    {
        return $this->stockIssued()->sum('quantity_issued');
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
            // Generate PID if not provided
            if (empty($stockAddition->pid)) {
                $stockAddition->pid = self::generateUniquePid();
            }

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

        static::created(function ($stockAddition) {
            // Log stock addition creation
            \App\Models\StockLog::logActivity(
                'created',
                "Stock addition created - {$stockAddition->total_pieces} pieces added",
                $stockAddition->id,
                null,
                null,
                null,
                null,
                ['total_pieces' => $stockAddition->total_pieces, 'total_sqft' => $stockAddition->total_sqft],
                $stockAddition->total_pieces,
                $stockAddition->total_sqft
            );
        });

        static::updating(function ($stockAddition) {
            // Check if there are any stock issues for this stock addition
            if ($stockAddition->stockIssued()->count() > 0) {
                // Only block updates to quantity/dimension fields
                // Product Name, Mine Vendor, and Particulars can always be updated
                $quantityDimensionFields = ['length', 'height', 'size_3d', 'total_pieces', 'total_sqft', 'weight', 'available_pieces', 'available_sqft', 'available_weight'];
                $hasQuantityDimensionChanges = false;
                
                foreach ($quantityDimensionFields as $field) {
                    if ($stockAddition->isDirty($field)) {
                        // Skip null values for sqft fields (form sends null but DB has 0.00)
                        if (($field === 'total_sqft' || $field === 'available_sqft') && 
                            $stockAddition->getAttribute($field) === null && 
                            $stockAddition->getOriginal($field) == 0) {
                            continue;
                        }
                        
                        $hasQuantityDimensionChanges = true;
                        \Log::info("Model: Quantity/dimension field change detected: {$field}", [
                            'old_value' => $stockAddition->getOriginal($field),
                            'new_value' => $stockAddition->getAttribute($field)
                        ]);
                        break;
                    }
                }
                
                if ($hasQuantityDimensionChanges) {
                    \Log::info('Model: Blocking update due to quantity/dimension field changes');
                    throw new \Exception('Cannot update stock dimensions or quantities after stock has been issued. Product Name, Mine Vendor, and Particulars can be updated freely.');
                }
                
                // Log that product/vendor/particulars fields are being updated for issued stock
                \Log::info('Updating product/vendor/particulars fields for issued stock', [
                    'stock_id' => $stockAddition->id,
                    'dirty_fields' => array_keys($stockAddition->getDirty()),
                    'has_stock_issuances' => $stockAddition->stockIssued()->count()
                ]);
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
     * Check if this stock is fully issued (no available pieces left).
     */
    public function isFullyIssued(): bool
    {
        return $this->available_pieces <= 0;
    }

    /**
     * Check if this stock is partially issued (some pieces issued but some still available).
     */
    public function isPartiallyIssued(): bool
    {
        return $this->hasBeenIssued() && $this->available_pieces > 0;
    }

    /**
     * Check if this stock can be updated (no stock issuances exist).
     */
    public function canBeUpdated(): bool
    {
        return !$this->hasBeenIssued();
    }

    /**
     * Get fields that are quantity/dimension related and cannot be updated when stock has been issued.
     */
    public function getQuantityDimensionFields(): array
    {
        return [
            'length',
            'height', 
            'size_3d',
            'total_pieces',
            'total_sqft',
            'weight',
            'available_pieces',
            'available_sqft',
            'available_weight'
        ];
    }

    /**
     * Check if a specific field can be updated for issued stock.
     * Product Name, Mine Vendor, and Particulars can always be updated.
     */
    public function canUpdateField(string $field): bool
    {
        if (!$this->hasBeenIssued()) {
            return true; // Can update any field if stock hasn't been issued
        }
        
        // Only block quantity/dimension fields, allow everything else
        return !in_array($field, $this->getQuantityDimensionFields());
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

    /**
     * Generate a unique PID for new stock additions
     */
    public static function generateUniquePid(): string
    {
        $lastStock = self::orderBy('id', 'desc')->first();
        $nextId = $lastStock ? $lastStock->id + 1 : 1;
        
        return 'STK-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

}
