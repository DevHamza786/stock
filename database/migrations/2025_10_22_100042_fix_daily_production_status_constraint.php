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
        // For SQLite, we need to recreate the table to fix the constraint
        DB::statement('CREATE TABLE daily_production_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            stock_addition_id BIGINT UNSIGNED NULL,
            machine_name VARCHAR(255) NULL,
            operator_name VARCHAR(255) NULL,
            notes TEXT NULL,
            date DATETIME NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            stock_issued_id BIGINT UNSIGNED NULL,
            stone VARCHAR(255) NULL,
            status VARCHAR(255) NOT NULL DEFAULT "open" CHECK (status IN ("open", "closed")),
            wastage_sqft DECIMAL(10,2) NULL,
            deleted_at TIMESTAMP NULL
        )');

        // Copy data from old table to new table, fixing status values
        DB::statement('INSERT INTO daily_production_new 
            SELECT id, stock_addition_id, machine_name, operator_name, notes, date, 
                   created_at, updated_at, stock_issued_id, stone, 
                   CASE WHEN status = "close" THEN "closed" ELSE status END,
                   wastage_sqft, deleted_at 
            FROM daily_production');

        // Drop old table and rename new table
        DB::statement('DROP TABLE daily_production');
        DB::statement('ALTER TABLE daily_production_new RENAME TO daily_production');

        // Recreate indexes
        DB::statement('CREATE INDEX daily_production_stock_addition_id_foreign ON daily_production (stock_addition_id)');
        DB::statement('CREATE INDEX daily_production_stock_issued_id_foreign ON daily_production (stock_issued_id)');
        DB::statement('CREATE INDEX daily_production_deleted_at_index ON daily_production (deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible due to SQLite limitations
        // If needed, restore from backup
    }
};