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
        Schema::table('vendor_bill_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_bill_payments', 'cash_payment_voucher_line_id')) {
                $table->foreignId('cash_payment_voucher_line_id')
                    ->nullable()
                    ->after('bank_payment_voucher_line_id')
                    ->constrained('cash_payment_voucher_lines')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_bill_payments', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_bill_payments', 'cash_payment_voucher_line_id')) {
                $table->dropForeign(['cash_payment_voucher_line_id']);
                $table->dropColumn('cash_payment_voucher_line_id');
            }
        });
    }
};

