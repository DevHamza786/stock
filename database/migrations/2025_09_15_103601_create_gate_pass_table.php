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
        Schema::create('gate_pass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_issued_id')->constrained('stock_issued')->onDelete('cascade');
            $table->integer('quantity_issued');
            $table->decimal('sqft_issued', 10, 2);
            $table->string('destination')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('date');
            $table->timestamps();

            $table->index(['stock_issued_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_pass');
    }
};
