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
        Schema::create('gate_pass_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_pass_id')->constrained('gate_pass')->onDelete('cascade');
            $table->foreignId('stock_addition_id')->constrained('stock_additions')->onDelete('cascade');
            $table->integer('quantity_issued');
            $table->decimal('sqft_issued', 10, 2);
            $table->decimal('weight_issued', 10, 2)->nullable();
            $table->string('stone')->nullable();
            $table->timestamps();

            $table->index(['gate_pass_id', 'stock_addition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_pass_items');
    }
};
