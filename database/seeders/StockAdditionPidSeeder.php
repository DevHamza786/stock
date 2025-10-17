<?php

namespace Database\Seeders;

use App\Models\StockAddition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockAdditionPidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Stock Addition PID seeding...');

        // Get all stock additions that don't have a PID
        $stockAdditionsWithoutPid = StockAddition::whereNull('pid')->get();

        if ($stockAdditionsWithoutPid->count() === 0) {
            $this->command->info('All stock additions already have PIDs. No action needed.');
            return;
        }

        $this->command->info("Found {$stockAdditionsWithoutPid->count()} stock additions without PIDs.");

        $updatedCount = 0;

        foreach ($stockAdditionsWithoutPid as $stockAddition) {
            try {
                // Generate a unique PID for this stock addition
                $pid = $this->generateUniquePid($stockAddition->id);
                
                // Update the stock addition with the PID
                $stockAddition->update(['pid' => $pid]);
                
                $updatedCount++;
                $this->command->info("Updated stock addition ID {$stockAddition->id} with PID: {$pid}");
                
            } catch (\Exception $e) {
                $this->command->error("Failed to update stock addition ID {$stockAddition->id}: " . $e->getMessage());
            }
        }

        $this->command->info("Successfully updated {$updatedCount} stock additions with PIDs.");

        // Verify all stock additions now have PIDs
        $remainingWithoutPid = StockAddition::whereNull('pid')->count();
        if ($remainingWithoutPid === 0) {
            $this->command->info('✅ All stock additions now have PIDs!');
        } else {
            $this->command->warn("⚠️  {$remainingWithoutPid} stock additions still don't have PIDs.");
        }
    }

    /**
     * Generate a unique PID based on stock addition ID
     * Format: STK-{padded_id} (e.g., STK-000001, STK-000123)
     */
    private function generateUniquePid(int $id): string
    {
        return 'STK-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if a PID already exists in the database
     */
    private function pidExists(string $pid): bool
    {
        return StockAddition::where('pid', $pid)->exists();
    }

    /**
     * Generate a unique PID that doesn't exist in the database
     */
    private function generateUniquePidSafe(int $id): string
    {
        $basePid = $this->generateUniquePid($id);
        
        // If the base PID doesn't exist, use it
        if (!$this->pidExists($basePid)) {
            return $basePid;
        }

        // If it exists, try with a suffix
        $counter = 1;
        do {
            $pid = 'STK-' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        } while ($this->pidExists($pid) && $counter < 100);

        if ($counter >= 100) {
            throw new \Exception("Could not generate unique PID for stock addition ID {$id}");
        }

        return $pid;
    }
}
