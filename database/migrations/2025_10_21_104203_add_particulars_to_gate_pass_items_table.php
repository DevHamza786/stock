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
            $table->text('particulars')->nullable()->after('stone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gate_pass_items', function (Blueprint $table) {
            $table->dropColumn('particulars');
        });
    }
};
