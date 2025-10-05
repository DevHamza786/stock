<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_issued', function (Blueprint $table) {
            // Add foreign key columns
            $table->foreignId('machine_id')->nullable()->after('purpose')->constrained('machines')->onDelete('set null');
            $table->foreignId('operator_id')->nullable()->after('machine_id')->constrained('operators')->onDelete('set null');
        });

        // Migrate existing data from machine_name/operator_name to foreign keys
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_issued', function (Blueprint $table) {
            $table->dropForeign(['machine_id']);
            $table->dropForeign(['operator_id']);
            $table->dropColumn(['machine_id', 'operator_id']);
        });
    }

    /**
     * Migrate existing machine_name and operator_name to foreign key relationships
     */
    private function migrateExistingData(): void
    {
        // Update machine_id based on existing machine_name
        $machineUpdates = DB::table('stock_issued')
            ->join('machines', 'stock_issued.machine_name', '=', 'machines.name')
            ->whereNotNull('stock_issued.machine_name')
            ->select('stock_issued.id', 'machines.id as machine_id')
            ->get();

        foreach ($machineUpdates as $update) {
            DB::table('stock_issued')
                ->where('id', $update->id)
                ->update(['machine_id' => $update->machine_id]);
        }

        // Update operator_id based on existing operator_name
        $operatorUpdates = DB::table('stock_issued')
            ->join('operators', 'stock_issued.operator_name', '=', 'operators.name')
            ->whereNotNull('stock_issued.operator_name')
            ->select('stock_issued.id', 'operators.id as operator_id')
            ->get();

        foreach ($operatorUpdates as $update) {
            DB::table('stock_issued')
                ->where('id', $update->id)
                ->update(['operator_id' => $update->operator_id]);
        }
    }
};