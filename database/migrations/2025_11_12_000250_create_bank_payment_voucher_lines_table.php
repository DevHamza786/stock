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
        Schema::create('bank_payment_voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_payment_voucher_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')
                ->constrained('chart_of_accounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('vendor_bill_id')
                ->nullable()
                ->constrained('vendor_bills')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('entry_type', 2);
            $table->decimal('amount', 12, 2);
            $table->string('particulars', 1000)->nullable();
            $table->string('cheque_no', 100)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bill_adjustment', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_payment_voucher_lines');
    }
};

