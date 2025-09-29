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
            // Add new length and height fields
            $table->decimal('length', 8, 2)->nullable()->after('stone');
            $table->decimal('height', 8, 2)->nullable()->after('length');
            
            // Keep size_3d for backward compatibility but make it nullable
            $table->string('size_3d')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_additions', function (Blueprint $table) {
            // Remove the new fields
            $table->dropColumn(['length', 'height']);
            
            // Make size_3d required again
            $table->string('size_3d')->nullable(false)->change();
        });
    }
};
