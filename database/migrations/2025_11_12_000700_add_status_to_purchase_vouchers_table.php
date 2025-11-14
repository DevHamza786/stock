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
        Schema::table('purchase_vouchers', function (Blueprint $table) {
            $table->enum('status', ['draft', 'posted'])->default('draft')->after('voucher_number');
            $table->foreignId('stock_addition_id')->nullable()->after('vendor_bill_id')->constrained('stock_additions')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_vouchers', function (Blueprint $table) {
            $table->dropForeign(['stock_addition_id']);
            $table->dropColumn(['status', 'stock_addition_id']);
        });
    }
};

