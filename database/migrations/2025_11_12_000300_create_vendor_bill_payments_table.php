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
        Schema::create('vendor_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')
                ->constrained('vendor_bills')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('bank_payment_voucher_line_id')
                ->nullable()
                ->constrained('bank_payment_voucher_lines')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('applied_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bill_payments');
    }
};

