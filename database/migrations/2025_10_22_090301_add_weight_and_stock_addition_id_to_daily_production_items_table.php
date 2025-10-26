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
        Schema::table('daily_production_items', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->after('product_name')->comment('Weight per piece in kg');
            $table->unsignedBigInteger('stock_addition_id')->nullable()->after('weight')->comment('Reference to stock addition');
            $table->foreign('stock_addition_id')->references('id')->on('stock_additions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_production_items', function (Blueprint $table) {
            $table->dropForeign(['stock_addition_id']);
            $table->dropColumn(['weight', 'stock_addition_id']);
        });
    }
};
