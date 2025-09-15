<?php

namespace Database\Seeders;

use App\Models\ConditionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConditionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditionStatuses = [
            [
                'name' => 'Block',
                'description' => 'Raw stone blocks from quarry',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Slabs',
                'description' => 'Cut stone slabs ready for processing',
                'color' => '#10B981',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Polished',
                'description' => 'Finished polished stone products',
                'color' => '#8B5CF6',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Rough',
                'description' => 'Rough cut stone pieces',
                'color' => '#F59E0B',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Tiles',
                'description' => 'Cut stone tiles for flooring/walls',
                'color' => '#EF4444',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Countertops',
                'description' => 'Finished countertop pieces',
                'color' => '#06B6D4',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Monuments',
                'description' => 'Memorial and monument pieces',
                'color' => '#F97316',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Landscaping',
                'description' => 'Stone pieces for landscaping',
                'color' => '#84CC16',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($conditionStatuses as $status) {
            ConditionStatus::create($status);
        }
    }
}
