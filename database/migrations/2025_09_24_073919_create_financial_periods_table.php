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
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_name'); // e.g., "2024", "Q1 2024", "January 2024"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period_type', ['YEAR', 'QUARTER', 'MONTH', 'CUSTOM']);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index(['is_closed', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_periods');
    }
};
