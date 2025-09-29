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
        // Convert existing size_3d data to length and height
        $stockAdditions = DB::table('stock_additions')
            ->whereNotNull('size_3d')
            ->where('size_3d', '!=', '')
            ->get();

        foreach ($stockAdditions as $stock) {
            $size3d = $stock->size_3d;
            
            // Extract dimensions from size_3d format (e.g., 20143 = 20x14x3)
            if (strlen($size3d) >= 4) {
                $length = (int) substr($size3d, 0, 2);
                $height = (int) substr($size3d, 2, 2);
                
                // Update the record with extracted dimensions
                DB::table('stock_additions')
                    ->where('id', $stock->id)
                    ->update([
                        'length' => $length,
                        'height' => $height,
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the converted data
        DB::table('stock_additions')
            ->whereNotNull('length')
            ->whereNotNull('height')
            ->update([
                'length' => null,
                'height' => null,
                'updated_at' => now()
            ]);
    }
};
