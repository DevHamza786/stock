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
        Schema::table('mine_vendors', function (Blueprint $table) {
            $table->foreignId('chart_of_account_id')->nullable()->after('id')->constrained('chart_of_accounts')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mine_vendors', function (Blueprint $table) {
            $table->dropForeign(['chart_of_account_id']);
            $table->dropColumn('chart_of_account_id');
        });
    }
};

