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
        Schema::create('daily_production', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_addition_id')->constrained()->onDelete('cascade');
            $table->string('machine_name');
            $table->string('product');
            $table->string('operator_name');
            $table->integer('total_pieces');
            $table->decimal('total_sqft', 10, 2);
            $table->string('condition_status');
            $table->text('notes')->nullable();
            $table->timestamp('date');
            $table->timestamps();

            $table->index(['stock_addition_id', 'date']);
            $table->index(['machine_name', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_production');
    }
};
