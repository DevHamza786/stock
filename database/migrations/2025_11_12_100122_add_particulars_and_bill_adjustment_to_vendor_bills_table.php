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
        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->text('particulars')->nullable()->after('notes');
            $table->string('bill_adjustment', 255)->nullable()->after('particulars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->dropColumn(['particulars', 'bill_adjustment']);
        });
    }
};
