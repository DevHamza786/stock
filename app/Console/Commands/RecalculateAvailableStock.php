<?php

namespace App\Console\Commands;

use App\Models\StockAddition;
use Illuminate\Console\Command;

class RecalculateAvailableStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:recalculate-available';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate available pieces for all stock additions based on issued stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to recalculate available stock...');
        
        $stockAdditions = StockAddition::all();
        $updated = 0;
        
        $this->withProgressBar($stockAdditions, function ($stockAddition) use (&$updated) {
            $oldAvailablePieces = $stockAddition->available_pieces;
            $oldAvailableSqft = $stockAddition->available_sqft;
            
            $stockAddition->recalculateAvailablePieces();
            
            if ($oldAvailablePieces != $stockAddition->available_pieces || 
                $oldAvailableSqft != $stockAddition->available_sqft) {
                $updated++;
            }
        });
        
        $this->newLine();
        $this->info("Recalculation complete! Updated {$updated} stock additions.");
        
        return Command::SUCCESS;
    }
}
