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
        Schema::create('stock_issued', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_addition_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_issued');
            $table->decimal('sqft_issued', 10, 2);
            $table->string('purpose')->nullable(); // Production, Sale, etc.
            $table->text('notes')->nullable();
            $table->timestamp('date');
            $table->timestamps();

            $table->index(['stock_addition_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_issued');
    }
};
