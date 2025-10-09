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
        // Clean non-numeric values from decimal fields in stock_additions
        $records = DB::table('stock_additions')->get();
        
        foreach ($records as $record) {
            $updates = [];
            
            // Clean length field
            if ($record->length !== null && !is_numeric($record->length)) {
                $cleaned = $this->extractNumericValue($record->length);
                $updates['length'] = $cleaned;
            }
            
            // Clean height field
            if ($record->height !== null && !is_numeric($record->height)) {
                $cleaned = $this->extractNumericValue($record->height);
                $updates['height'] = $cleaned;
            }
            
            // Clean diameter field
            if ($record->diameter !== null && !is_numeric($record->diameter)) {
                $cleaned = $this->extractNumericValue($record->diameter);
                $updates['diameter'] = $cleaned;
            }
            
            // Clean weight field
            if ($record->weight !== null && !is_numeric($record->weight)) {
                $cleaned = $this->extractNumericValue($record->weight);
                $updates['weight'] = $cleaned;
            }
            
            // Update the record if there are changes
            if (!empty($updates)) {
                DB::table('stock_additions')
                    ->where('id', $record->id)
                    ->update($updates);
            }
        }
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
