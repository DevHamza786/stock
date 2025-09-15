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
        Schema::create('stock_additions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('mine_vendor_id')->constrained()->onDelete('cascade');
            $table->string('stone');
            $table->string('size_3d'); // 3D dimensions like 20143
            $table->integer('total_pieces');
            $table->decimal('total_sqft', 10, 2); // Auto-calculated
            $table->string('condition_status'); // Block, Slabs, Polished
            $table->integer('available_pieces')->default(0); // Remaining pieces after issuances
            $table->decimal('available_sqft', 10, 2)->default(0); // Remaining sqft after issuances
            $table->timestamp('date');
            $table->timestamps();

            $table->index(['product_id', 'date']);
            $table->index(['mine_vendor_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_additions');
    }
};
