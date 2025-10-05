<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_additions', function (Blueprint $table) {
            // Make total_sqft nullable for block conditions
            $table->decimal('total_sqft', 10, 2)->nullable()->change();
            
            // Also make available_sqft nullable for consistency
            $table->decimal('available_sqft', 10, 2)->nullable()->change();
            
            // Make size_3d nullable as it's not needed for blocks
            $table->string('size_3d')->nullable()->change();
            
            // Add weight column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'weight')) {
                $table->decimal('weight', 10, 2)->nullable()->after('diameter');
            }
            
            // Add available_weight column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'available_weight')) {
                $table->decimal('available_weight', 10, 2)->nullable()->after('available_sqft');
            }
            
            // Add diameter column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'diameter')) {
                $table->string('diameter')->nullable()->after('height');
            }
            
            // Add length column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'length')) {
                $table->decimal('length', 10, 2)->nullable()->after('stone');
            }
            
            // Add height column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'height')) {
                $table->decimal('height', 10, 2)->nullable()->after('length');
            }
            
            // Add pid column if it doesn't exist
            if (!Schema::hasColumn('stock_additions', 'pid')) {
                $table->string('pid', 20)->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_additions', function (Blueprint $table) {
            // Revert total_sqft to not nullable
            $table->decimal('total_sqft', 10, 2)->nullable(false)->change();
            
            // Revert available_sqft to not nullable
            $table->decimal('available_sqft', 10, 2)->nullable(false)->change();
            
            // Revert size_3d to not nullable
            $table->string('size_3d')->nullable(false)->change();
        });
    }
};