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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique(); // e.g., JE-2024-001
            $table->date('entry_date');
            $table->text('description');
            $table->enum('entry_type', [
                'MANUAL',           // Manual journal entry
                'AUTO_STOCK_ADD',   // Auto-generated for stock additions
                'AUTO_STOCK_ISSUE', // Auto-generated for stock issued
                'AUTO_PRODUCTION',  // Auto-generated for daily production
                'AUTO_GATE_PASS',   // Auto-generated for gate pass
                'AUTO_SALE',        // Auto-generated for sales
                'AUTO_PURCHASE',    // Auto-generated for purchases
                'ADJUSTMENT'        // Adjustment entries
            ]);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->enum('status', ['DRAFT', 'POSTED', 'REVERSED'])->default('DRAFT');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['entry_date', 'status']);
            $table->index(['entry_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
