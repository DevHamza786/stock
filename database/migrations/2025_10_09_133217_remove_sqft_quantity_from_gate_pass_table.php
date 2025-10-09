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
        Schema::table('gate_pass', function (Blueprint $table) {
            $table->dropColumn(['sqft_issued', 'quantity_issued']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_pass', function (Blueprint $table) {
            $table->integer('quantity_issued')->default(0);
            $table->decimal('sqft_issued', 10, 2)->default(0);
        });
    }
};
