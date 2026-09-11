<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@circularwastehub.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '0700000000',
                'business_name' => 'Circular Waste Hub',
                'is_verified' => true,
            ]
        );

        $this->command->info('✅ Admin user created: admin@circularwastehub.com / password');
    }
}