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
        Schema::table('daily_production', function (Blueprint $table) {
            // Remove old columns that are now handled by daily_production_items table
            $table->dropColumn([
                'product',
                'total_pieces',
                'total_sqft',
                'condition_status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_production', function (Blueprint $table) {
            // Add back the old columns
            $table->string('product');
            $table->integer('total_pieces');
            $table->decimal('total_sqft', 10, 2);
            $table->string('condition_status');
        });
    }
};
