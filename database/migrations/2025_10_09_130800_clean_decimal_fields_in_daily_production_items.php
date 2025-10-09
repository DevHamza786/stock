<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean non-numeric values from decimal fields in daily_production_items
        $records = DB::table('daily_production_items')->get();
        
        foreach ($records as $record) {
            $updates = [];
            
            // Clean total_sqft field
            if ($record->total_sqft !== null && !is_numeric($record->total_sqft)) {
                $cleaned = $this->extractNumericValue($record->total_sqft);
                $updates['total_sqft'] = $cleaned;
            }
            
            // Clean total_weight field
            if ($record->total_weight !== null && !is_numeric($record->total_weight)) {
                $cleaned = $this->extractNumericValue($record->total_weight);
                $updates['total_weight'] = $cleaned;
            }
            
            // Update the record if there are changes
            if (!empty($updates)) {
                DB::table('daily_production_items')
                    ->where('id', $record->id)
                    ->update($updates);
            }
        }
        
        // Also clean empty strings
        DB::table('daily_production_items')->whereRaw("total_sqft = ''")->update(['total_sqft' => null]);
        DB::table('daily_production_items')->whereRaw("total_weight = ''")->update(['total_weight' => null]);
    }

    /**
     * Extract numeric value from a string (e.g., "2 cm" -> 2, "5.5 kg" -> 5.5)
     */
    private function extractNumericValue($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Remove common units and text
        $cleaned = preg_replace('/[^0-9.]/', '', $value);
        
        // Check if we have a valid number
        if ($cleaned !== '' && is_numeric($cleaned)) {
            return (float) $cleaned;
        }
        
        return null;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data cleanup
    }
};
