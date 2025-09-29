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
        Schema::table('stock_issued', function (Blueprint $table) {
            $table->decimal('weight_issued', 10, 2)->nullable()->after('sqft_issued');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_issued', function (Blueprint $table) {
            $table->dropColumn('weight_issued');
        });
    }
};
