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
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chart_of_account_id')
                ->constrained('chart_of_accounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('vendor_reference')->nullable();
            $table->string('bill_number')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('balance_amount', 12, 2);
            $table->enum('status', ['open', 'partial', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bills');
    }
};

