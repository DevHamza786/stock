<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DailyProductionItem;
use App\Models\DailyProduction;
use App\Models\StockIssued;
use App\Models\StockAddition;
use Illuminate\Support\Facades\DB;

class UpdateDailyProductionItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to update daily production items with weight and stock_addition_id...');

        // Get all daily production items that don't have stock_addition_id
        $itemsWithoutStockAdditionId = DailyProductionItem::whereNull('stock_addition_id')
            ->with('dailyProduction')
            ->get();

        $this->command->info("Found {$itemsWithoutStockAdditionId->count()} items without stock_addition_id");

        $updatedCount = 0;
        $errorCount = 0;

        foreach ($itemsWithoutStockAdditionId as $item) {
            try {
                // Get the daily production record
                $dailyProduction = $item->dailyProduction;
                
                if (!$dailyProduction) {
                    $this->command->warn("Daily production not found for item ID: {$item->id}");
                    $errorCount++;
                    continue;
                }

                // Get the stock issued record
                $stockIssued = StockIssued::find($dailyProduction->stock_issued_id);
                
                if (!$stockIssued) {
                    $this->command->warn("Stock issued not found for daily production ID: {$dailyProduction->id}");
                    $errorCount++;
                    continue;
                }

                // Calculate weight per piece for block/monuments items
                $weight = null;
                if ($item->condition_status && 
                    in_array(strtolower($item->condition_status), ['block', 'monuments']) &&
                    $item->total_weight && 
                    $item->total_pieces > 0) {
                    $weight = $item->total_weight / $item->total_pieces;
                }

                // Create a new stock addition record for this production item
                $newStockAddition = $this->createStockAdditionForProductionItem($item, $stockIssued);

                // Update the item with the new stock addition ID and weight
                $item->update([
                    'stock_addition_id' => $newStockAddition->id,
                    'weight' => $weight
                ]);

                $updatedCount++;
                
                $this->command->info("Updated item ID: {$item->id} - Product: {$item->product_name}, Weight: " . ($weight ? number_format($weight, 2) . ' kg' : 'N/A') . ", New Stock Addition PID: {$newStockAddition->pid}");

            } catch (\Exception $e) {
                $this->command->error("Error updating item ID {$item->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->command->info("Update completed!");
        $this->command->info("Successfully updated: {$updatedCount} items");
        $this->command->info("Errors encountered: {$errorCount} items");

        // Also update items that might have stock_addition_id but missing weight
        $this->updateMissingWeights();
    }

    /**
     * Update items that have stock_addition_id but missing weight
     */
    private function updateMissingWeights(): void
    {
        $this->command->info('Checking for items with missing weight...');

        $itemsWithMissingWeight = DailyProductionItem::whereNotNull('stock_addition_id')
            ->whereNull('weight')
            ->where(function($query) {
                $query->where('condition_status', 'block')
                      ->orWhere('condition_status', 'monuments');
            })
            ->get();

        $this->command->info("Found {$itemsWithMissingWeight->count()} block/monuments items with missing weight");

        $weightUpdatedCount = 0;

        foreach ($itemsWithMissingWeight as $item) {
            if ($item->total_weight && $item->total_pieces > 0) {
                $weight = $item->total_weight / $item->total_pieces;
                
                $item->update(['weight' => $weight]);
                $weightUpdatedCount++;
                
                $this->command->info("Updated weight for item ID: {$item->id} - Weight: " . number_format($weight, 2) . ' kg');
            }
        }

        $this->command->info("Weight updates completed: {$weightUpdatedCount} items updated");
    }

    /**
     * Create a new stock addition record for a production item
     */
    private function createStockAdditionForProductionItem($item, $originalStockIssued)
    {
        $originalStockAddition = $originalStockIssued->stockAddition;
        
        // Determine if this is a block/monuments or sqft-based product
        $isBlockOrMonuments = $item->condition_status && 
            in_array(strtolower($item->condition_status), ['block', 'monuments']);

        // Generate a unique PID for the new stock addition
        $pid = $this->generateUniquePID();

        // Create the new stock addition
        $newStockAddition = StockAddition::create([
            'pid' => $pid,
            'product_id' => $originalStockAddition->product_id,
            'mine_vendor_id' => $originalStockAddition->mine_vendor_id,
            'stone' => $item->product_name,
            'condition_status' => $item->condition_status,
            'length' => $isBlockOrMonuments ? null : $this->extractLengthFromSize($item->size),
            'height' => $isBlockOrMonuments ? null : $this->extractHeightFromSize($item->size),
            'diameter' => $item->diameter,
            'weight' => $isBlockOrMonuments ? $item->total_weight : null,
            'total_pieces' => $item->total_pieces,
            'total_sqft' => $isBlockOrMonuments ? null : $item->total_sqft,
            'available_sqft' => $isBlockOrMonuments ? null : $item->total_sqft,
            'available_weight' => $isBlockOrMonuments ? $item->total_weight : 0,
            'available_pieces' => $item->total_pieces,
            'date' => $item->dailyProduction->date,
        ]);

        return $newStockAddition;
    }

    /**
     * Generate a unique PID for stock addition
     */
    private function generateUniquePID()
    {
        do {
            $pid = 'STK-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (StockAddition::where('pid', $pid)->exists());

        return $pid;
    }

    /**
     * Extract height from size string (e.g., "21*60" -> 21)
     */
    private function extractHeightFromSize($size)
    {
        if (!$size) return null;
        
        $sizeMatch = preg_match('/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i', $size, $matches);
        return $sizeMatch ? floatval($matches[1]) : null;
    }

    /**
     * Extract length from size string (e.g., "21*60" -> 60)
     */
    private function extractLengthFromSize($size)
    {
        if (!$size) return null;
        
        $sizeMatch = preg_match('/(\d+(?:\.\d+)?)\s*[×*x]\s*(\d+(?:\.\d+)?)/i', $size, $matches);
        return $sizeMatch ? floatval($matches[2]) : null;
    }
}
