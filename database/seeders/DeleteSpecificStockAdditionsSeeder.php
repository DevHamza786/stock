<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeleteSpecificStockAdditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // IDs to delete
        $idsToDelete = [
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

        \Log::info('Deleting stock additions', ['ids' => $idsToDelete]);

        // Delete the specified stock additions
        $deletedCount = \App\Models\StockAddition::whereIn('id', $idsToDelete)->delete();

        \Log::info('Stock additions deleted', ['count' => $deletedCount]);

        echo "Deleted {$deletedCount} stock addition records.\n";
    }
}
