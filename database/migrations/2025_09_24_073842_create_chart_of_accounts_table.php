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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique(); // e.g., 1000, 1100, 2000
            $table->string('account_name');
            $table->text('description')->nullable();
            $table->enum('account_type', [
                'ASSET',           // Assets
                'LIABILITY',       // Liabilities
                'EQUITY',          // Owner's Equity
                'REVENUE',         // Income/Revenue
                'EXPENSE'          // Expenses
            ]);
            $table->enum('account_subtype', [
                // Assets
                'CURRENT_ASSET', 'FIXED_ASSET', 'INVENTORY', 'ACCOUNTS_RECEIVABLE', 'CASH',
                // Liabilities
                'CURRENT_LIABILITY', 'LONG_TERM_LIABILITY', 'ACCOUNTS_PAYABLE',
                // Equity
                'OWNER_EQUITY', 'RETAINED_EARNINGS', 'CAPITAL',
                // Revenue
                'SALES_REVENUE', 'SERVICE_REVENUE', 'OTHER_INCOME',
                // Expenses
                'COST_OF_GOODS_SOLD', 'OPERATING_EXPENSE', 'ADMINISTRATIVE_EXPENSE', 'OTHER_EXPENSE'
            ]);
            $table->enum('normal_balance', ['DEBIT', 'CREDIT']);
            $table->foreignId('parent_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('cascade');
            $table->integer('level')->default(1); // 1 = Main account, 2 = Sub account, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_account')->default(false); // System generated accounts
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
            $table->index(['parent_account_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
