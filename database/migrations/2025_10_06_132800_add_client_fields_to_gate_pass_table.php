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
            $table->string('client_name')->nullable()->after('driver_name');
            $table->string('client_number')->nullable()->after('client_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_pass', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'client_number']);
        });
    }
};
