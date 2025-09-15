<?php

namespace Database\Seeders;

use App\Models\MineVendor;
use Illuminate\Database\Seeder;

class MineVendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'ABC Stone Quarry',
                'contact_person' => 'John Smith',
                'phone' => '+1-555-0123',
                'email' => 'john@abcstone.com',
                'address' => '123 Quarry Road, Stone City, SC 12345',
                'is_active' => true,
            ],
            [
                'name' => 'XYZ Marble Works',
                'contact_person' => 'Sarah Johnson',
                'phone' => '+1-555-0456',
                'email' => 'sarah@xyzmarbles.com',
                'address' => '456 Marble Lane, Granite Town, GT 67890',
                'is_active' => true,
            ],
            [
                'name' => 'Premium Granite Co.',
                'contact_person' => 'Mike Wilson',
                'phone' => '+1-555-0789',
                'email' => 'mike@premiumgranite.com',
                'address' => '789 Granite Street, Rock City, RC 54321',
                'is_active' => true,
            ],
            [
                'name' => 'Natural Stone Ltd.',
                'contact_person' => 'Emily Davis',
                'phone' => '+1-555-0321',
                'email' => 'emily@naturalstone.com',
                'address' => '321 Stone Avenue, Mineral Valley, MV 98765',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Marble Inc.',
                'contact_person' => 'David Brown',
                'phone' => '+1-555-0654',
                'email' => 'david@qualitymarble.com',
                'address' => '654 Marble Drive, Crystal Springs, CS 13579',
                'is_active' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            MineVendor::create($vendor);
        }
    }
}
