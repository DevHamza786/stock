<?php

namespace App\Console\Commands;

use App\Models\StockAddition;
use Illuminate\Console\Command;

class AddPidToStockAdditions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:add-pids {--force : Force update even if PIDs already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add PIDs to stock additions that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting PID addition process...');

        $query = StockAddition::query();
        
        if (!$this->option('force')) {
            $query->whereNull('pid');
        }

        $stockAdditions = $query->get();

        if ($stockAdditions->count() === 0) {
            $this->info('All stock additions already have PIDs. Use --force to regenerate all PIDs.');
            return 0;
        }

        $this->info("Found {$stockAdditions->count()} stock additions to process.");

        $progressBar = $this->output->createProgressBar($stockAdditions->count());
        $progressBar->start();

        $updatedCount = 0;
        $errors = [];

        foreach ($stockAdditions as $stockAddition) {
            try {
                // Generate a unique PID for this stock addition
                $pid = $this->generateUniquePid($stockAddition->id);
                
                // Update the stock addition with the PID
                $stockAddition->update(['pid' => $pid]);
                
                $updatedCount++;
                
            } catch (\Exception $e) {
                $errors[] = "ID {$stockAddition->id}: " . $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if ($updatedCount > 0) {
            $this->info("✅ Successfully updated {$updatedCount} stock additions with PIDs.");
        }

        if (!empty($errors)) {
            $this->error("❌ Errors occurred:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        // Verify all stock additions now have PIDs
        $remainingWithoutPid = StockAddition::whereNull('pid')->count();
        if ($remainingWithoutPid === 0) {
            $this->info('🎉 All stock additions now have PIDs!');
        } else {
            $this->warn("⚠️  {$remainingWithoutPid} stock additions still don't have PIDs.");
        }

        return 0;
    }

    /**
     * Generate a unique PID based on stock addition ID
     * Format: STK-{padded_id} (e.g., STK-000001, STK-000123)
     */
    private function generateUniquePid(int $id): string
    {
        return 'STK-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }
}
