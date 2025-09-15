<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Marble White',
                'description' => 'Premium white marble with fine grain',
                'category' => 'Marble',
                'is_active' => true,
            ],
            [
                'name' => 'Granite Black',
                'description' => 'High-quality black granite',
                'category' => 'Granite',
                'is_active' => true,
            ],
            [
                'name' => 'Marble Pink',
                'description' => 'Beautiful pink marble with unique patterns',
                'category' => 'Marble',
                'is_active' => true,
            ],
            [
                'name' => 'Granite Grey',
                'description' => 'Durable grey granite for construction',
                'category' => 'Granite',
                'is_active' => true,
            ],
            [
                'name' => 'Marble Green',
                'description' => 'Elegant green marble with natural veining',
                'category' => 'Marble',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
