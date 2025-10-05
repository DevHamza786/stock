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
        Schema::table('stock_additions', function (Blueprint $table) {
            // Add PID column as unique string
            $table->string('pid')->unique()->nullable()->after('id');
            
            // Add index for better performance
            $table->index('pid');
        });

        // Generate PIDs for existing records
        $this->generatePidsForExistingRecords();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_additions', function (Blueprint $table) {
            $table->dropIndex(['pid']);
            $table->dropColumn('pid');
        });
    }

    /**
     * Generate PIDs for existing stock addition records
     */
    private function generatePidsForExistingRecords(): void
    {
        $stockAdditions = DB::table('stock_additions')->orderBy('id')->get();
        
        foreach ($stockAdditions as $stock) {
            $pid = $this->generateUniquePid($stock->id);
            DB::table('stock_additions')
                ->where('id', $stock->id)
                ->update(['pid' => $pid]);
        }
    }

    /**
     * Generate a unique PID based on stock addition ID
     * Format: STK-{padded_id} (e.g., STK-000001, STK-000123)
     */
    private function generateUniquePid(int $id): string
    {
        return 'STK-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
};