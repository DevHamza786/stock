<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeleteDailyProductionByStockAdditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // IDs to delete based on stock_addition_id
        $stockAdditionIds = [
            50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60,
            63, 64, 65, 66,
            68, 69, 70, 71,
            73, 74,
            78, 79,
            81, 82,
            89, 90, 91, 92, 93,
            94, 95, 96, 97,
            98, 99, 100, 101,
            102, 103, 104, 105, 106
        ];

        \Log::info('Deleting daily productions by stock_addition_id', ['stock_addition_ids' => $stockAdditionIds]);

        // Delete the daily production records
        $deletedCount = \App\Models\DailyProduction::whereIn('stock_addition_id', $stockAdditionIds)->delete();

        \Log::info('Daily productions deleted', ['count' => $deletedCount]);

        echo "Deleted {$deletedCount} daily production records.\n";
    }
}
