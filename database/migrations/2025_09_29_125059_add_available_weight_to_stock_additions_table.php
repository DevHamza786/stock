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
            $table->decimal('available_weight', 10, 2)->default(0)->after('available_sqft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_additions', function (Blueprint $table) {
            $table->dropColumn('available_weight');
        });
    }
};
