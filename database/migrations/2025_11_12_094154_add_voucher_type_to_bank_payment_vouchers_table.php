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
        Schema::table('bank_payment_vouchers', function (Blueprint $table) {
            $table->enum('voucher_type', ['payment', 'receipt'])->default('payment')->after('voucher_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('voucher_type');
        });
    }
};
