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
        Schema::create('daily_production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_production_id')->constrained('daily_production')->onDelete('cascade');
            $table->string('product_name');
            $table->string('size')->nullable(); // e.g., "60*90", "H*L"
            $table->string('diameter')->nullable(); // e.g., "6cm", "2cm"
            $table->string('condition_status');
            $table->string('special_status')->nullable(); // e.g., "Polished", "Hound", "Bushed"
            $table->integer('total_pieces');
            $table->decimal('total_sqft', 10, 2);
            $table->text('narration')->nullable();
            $table->timestamps();

            $table->index(['daily_production_id', 'product_name']);
            $table->index(['product_name', 'condition_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_production_items');
    }
};
