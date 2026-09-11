<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Seeder;

class WasteCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Organic Waste', 'slug' => 'organic', 'icon' => '🍌', 'color' => '#2D6A4F', 'display_order' => 1],
            ['name' => 'Electronics (E-Waste)', 'slug' => 'e-waste', 'icon' => '📱', 'color' => '#1B5E20', 'display_order' => 2],
            ['name' => 'Plastics', 'slug' => 'plastics', 'icon' => '🥤', 'color' => '#0288D1', 'display_order' => 3],
            ['name' => 'Paper & Cardboard', 'slug' => 'paper', 'icon' => '📦', 'color' => '#F57C00', 'display_order' => 4],
            ['name' => 'Metals', 'slug' => 'metals', 'icon' => '🔩', 'color' => '#757575', 'display_order' => 5],
            ['name' => 'Glass', 'slug' => 'glass', 'icon' => '🥃', 'color' => '#4DB6AC', 'display_order' => 6],
            ['name' => 'Textiles & Fabrics', 'slug' => 'textiles', 'icon' => '👕', 'color' => '#7B1FA2', 'display_order' => 7],
            ['name' => 'Rubber & Tires', 'slug' => 'rubber', 'icon' => '🚗', 'color' => '#3E2723', 'display_order' => 8],
            ['name' => 'Construction Waste', 'slug' => 'construction', 'icon' => '🏗️', 'color' => '#8D6E63', 'display_order' => 9],
            ['name' => 'Hazardous Waste', 'slug' => 'hazardous', 'icon' => '⚠️', 'color' => '#D32F2F', 'display_order' => 10],
            ['name' => 'Wood & Biomass', 'slug' => 'wood', 'icon' => '🪵', 'color' => '#795548', 'display_order' => 11],
            ['name' => 'Bio-Medical Waste', 'slug' => 'biomedical', 'icon' => '💊', 'color' => '#C2185B', 'display_order' => 12],
        ];

        foreach ($categories as $category) {
            WasteCategory::create($category);
        }
    }
}