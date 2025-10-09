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
        // Define tables and their decimal columns
        $tablesToClean = [
            'stock_issued' => ['sqft_issued', 'weight_issued'],
            'gate_pass' => ['sqft_issued'],
            'gate_pass_items' => ['sqft_issued', 'weight_issued'],
            'daily_production' => ['wastage_sqft'],
        ];

        foreach ($tablesToClean as $table => $columns) {
            foreach ($columns as $column) {
                // Clean empty strings
                DB::table($table)->whereRaw("$column = ''")->update([$column => null]);
                
                // Clean non-numeric values
                $records = DB::table($table)->whereNotNull($column)->get();
                
                foreach ($records as $record) {
                    $value = $record->{$column};
                    
                    if ($value !== null && !is_numeric($value)) {
                        $cleaned = $this->extractNumericValue($value);
                        DB::table($table)
                            ->where('id', $record->id)
                            ->update([$column => $cleaned]);
                    }
                }
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
