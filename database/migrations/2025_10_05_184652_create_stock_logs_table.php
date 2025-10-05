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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_addition_id');
            $table->unsignedBigInteger('stock_issued_id')->nullable();
            $table->unsignedBigInteger('gate_pass_id')->nullable();
            $table->unsignedBigInteger('daily_production_id')->nullable();
            $table->string('action_type'); // 'created', 'updated', 'deleted', 'issued', 'dispatched', 'produced'
            $table->string('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->integer('quantity_changed')->default(0);
            $table->decimal('sqft_changed', 10, 2)->default(0);
            $table->decimal('weight_changed', 10, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('stock_addition_id')->references('id')->on('stock_additions')->onDelete('cascade');
            $table->foreign('stock_issued_id')->references('id')->on('stock_issued')->onDelete('cascade');
            $table->foreign('gate_pass_id')->references('id')->on('gate_pass')->onDelete('cascade');
            $table->foreign('daily_production_id')->references('id')->on('daily_production')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
