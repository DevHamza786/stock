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
        Schema::table('gate_pass_items', function (Blueprint $table) {
            $table->string('pid')->nullable()->after('stock_addition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_pass_items', function (Blueprint $table) {
            $table->dropColumn('pid');
        });
    }
};
