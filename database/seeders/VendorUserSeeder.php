<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WasteListing;
use App\Models\WasteMatch;
use App\Models\WasteType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create Vendor User ─────────────────────────────────────
        $vendor = User::firstOrCreate(
            ['email' => 'vendor@agrismart.com'],
            [
                'name' => 'Mama Njeri',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'phone' => '0712345678',
                'business_name' => 'Njeri Fresh Produce',
                'market_area' => 'Marikiti',
                'rating_avg' => 4.5,
                'latitude' => -1.2921,
                'longitude' => 36.8219,
            ]
        );

        // ── Create Processor User ──────────────────────────────────
        $processor = User::firstOrCreate(
            ['email' => 'processor@agrismart.com'],
            [
                'name' => 'Kamau Compost',
                'password' => Hash::make('password'),
                'role' => 'processor',
                'phone' => '0798765432',
                'business_name' => 'GreenCycle Compost Ltd',
                'market_area' => 'Kawangware',
                'rating_avg' => 4.8,
                'latitude' => -1.2801,
                'longitude' => 36.7629,
            ]
        );

        // ── Create Admin User ──────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@agrismart.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '0700000000',
                'business_name' => 'Agri-Smart Admin',
                'market_area' => 'Nairobi',
                'rating_avg' => null,
                'latitude' => -1.2864,
                'longitude' => 36.8172,
            ]
        );

        // ── Get Waste Types ────────────────────────────────────────
        $fruitPeels = WasteType::where('name', 'Fruit Peels')->first();
        $vegTrimmings = WasteType::where('name', 'Vegetable Trimmings')->first();
        $mixedFood = WasteType::where('name', 'Mixed Food Waste')->first();

        // ── Sample Active Listing ──────────────────────────────────
        if ($fruitPeels) {
            WasteListing::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'waste_type_id' => $fruitPeels->id,
                    'status' => 'active'
                ],
                [
                    'quantity_kg' => 35,
                    'market_area' => 'Marikiti',
                    'availability_window' => 'today',
                    'expires_at' => now()->endOfDay(),
                ]
            );
        }

        // ── Sample Claimed Listing (In Progress) ───────────────────
        if ($vegTrimmings) {
            $claimedListing = WasteListing::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'waste_type_id' => $vegTrimmings->id,
                    'status' => 'claimed'
                ],
                [
                    'quantity_kg' => 20,
                    'market_area' => 'Marikiti',
                    'availability_window' => 'tomorrow',
                    'expires_at' => now()->addDay()->endOfDay(),
                ]
            );

            // Create a match for this claimed listing
            if ($claimedListing->wasRecentlyCreated) {
                WasteMatch::create([
                    'waste_listing_id' => $claimedListing->id,
                    'processor_id' => $processor->id,
                    'status' => 'pending',
                    'claimed_at' => now()->subHours(2),
                ]);
            }
        }

        // ── Sample Completed Listing (History) ─────────────────────
        if ($mixedFood) {
            $completedListing = WasteListing::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'waste_type_id' => $mixedFood->id,
                    'status' => 'completed'
                ],
                [
                    'quantity_kg' => 50,
                    'market_area' => 'Marikiti',
                    'availability_window' => 'this_week',
                    'expires_at' => now()->subDays(5),
                ]
            );

            // Create a completed match
            if ($completedListing->wasRecentlyCreated) {
                WasteMatch::create([
                    'waste_listing_id' => $completedListing->id,
                    'processor_id' => $processor->id,
                    'status' => 'completed',
                    'claimed_at' => now()->subDays(6),
                    'collected_at' => now()->subDays(5),
                    'completed_at' => now()->subDays(5),
                    'actual_quantity_kg' => 48,
                ]);
            }
        }

        $this->command->info('✅ VendorUserSeeder completed!');
        $this->command->info('');
        $this->command->info('📋 Test Credentials:');
        $this->command->info('   Vendor:    vendor@agrismart.com / password');
        $this->command->info('   Processor: processor@agrismart.com / password');
        $this->command->info('   Admin:     admin@agrismart.com / password');
    }
}