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
            $table->dropColumn(['machine_name', 'operator_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_issued', function (Blueprint $table) {
            $table->string('machine_name')->nullable()->after('machine_id');
            $table->string('operator_name')->nullable()->after('operator_id');
        });
    }
};