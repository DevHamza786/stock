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
            $table->foreignId('stock_issued_id')->nullable()->after('stock_addition_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_production', function (Blueprint $table) {
            $table->dropForeign(['stock_issued_id']);
            $table->dropColumn('stock_issued_id');
        });
    }
};
