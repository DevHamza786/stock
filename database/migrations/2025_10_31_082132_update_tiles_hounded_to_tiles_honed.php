<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates all records from "Tiles Hounded" to "Tiles Honed" 
     * in stock_additions and daily_production_items tables.
     */
    public function up(): void
    {
        // Update stock_additions table
        DB::table('stock_additions')
            ->where('condition_status', 'Tiles Hounded')
            ->update(['condition_status' => 'Tiles Honed']);
        
        // Update daily_production_items table
        DB::table('daily_production_items')
            ->where('condition_status', 'Tiles Hounded')
            ->update(['condition_status' => 'Tiles Honed']);
    }

    /**
     * Reverse the migrations.
     * Reverts all records from "Tiles Honed" back to "Tiles Hounded"
     */
    public function down(): void
    {
        // Revert stock_additions table
        DB::table('stock_additions')
            ->where('condition_status', 'Tiles Honed')
            ->update(['condition_status' => 'Tiles Hounded']);
        
        // Revert daily_production_items table
        DB::table('daily_production_items')
            ->where('condition_status', 'Tiles Honed')
            ->update(['condition_status' => 'Tiles Hounded']);
    }
};
