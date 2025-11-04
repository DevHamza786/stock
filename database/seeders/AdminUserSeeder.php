<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Ensure role is set to admin (in case user already existed)
        if ($admin->role !== 'admin') {
            $admin->role = 'admin';
            $admin->save();
        }

        $this->command->info('Admin user created/updated successfully!');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Password: admin123');
        $this->command->info('Role: ' . $admin->role);
    }
}
