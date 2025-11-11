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
            if (Schema::hasColumn('bank_payment_vouchers', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }

            if (! Schema::hasColumn('bank_payment_vouchers', 'bank_account_id')) {
                $table->foreignId('bank_account_id')
                    ->after('voucher_number')
                    ->constrained('chart_of_accounts')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_payment_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('bank_payment_vouchers', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }

            if (! Schema::hasColumn('bank_payment_vouchers', 'vendor_id')) {
                $table->foreignId('vendor_id')
                    ->after('payment_date')
                    ->constrained('mine_vendors')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            }
        });
    }
};

