<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use App\Models\WasteType;
use Illuminate\Database\Seeder;

class WasteTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Get all categories by slug
        $organic = WasteCategory::where('slug', 'organic')->first();
        $ewaste = WasteCategory::where('slug', 'e-waste')->first();
        $plastics = WasteCategory::where('slug', 'plastics')->first();
        $paper = WasteCategory::where('slug', 'paper')->first();
        $metals = WasteCategory::where('slug', 'metals')->first();
        $glass = WasteCategory::where('slug', 'glass')->first();
        $textiles = WasteCategory::where('slug', 'textiles')->first();
        $rubber = WasteCategory::where('slug', 'rubber')->first();
        $construction = WasteCategory::where('slug', 'construction')->first();
        $hazardous = WasteCategory::where('slug', 'hazardous')->first();
        $wood = WasteCategory::where('slug', 'wood')->first();
        $biomedical = WasteCategory::where('slug', 'biomedical')->first();

        // ==================== ORGANIC WASTE ====================
        if ($organic) {
            $types = [
                ['name' => 'Fruit Peels', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Vegetable Trimmings', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Coffee Husks', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Tea Waste', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Butchery Remains (Bones/Fat)', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Meat Processing Waste', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Fish Processing Waste', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Food Scraps (Restaurants)', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Bakery Waste', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Brewery Waste', 'unit' => 'kg', 'min_quantity' => 200],
                ['name' => 'Mixed Food Waste', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Spoiled Produce', 'unit' => 'kg', 'min_quantity' => 20],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $organic->id, 'is_active' => true]));
            }
        }

        // ==================== E-WASTE ====================
        if ($ewaste) {
            $types = [
                ['name' => 'Computers & Laptops', 'unit' => 'pieces', 'min_quantity' => 5],
                ['name' => 'Phones & Tablets', 'unit' => 'pieces', 'min_quantity' => 10],
                ['name' => 'TVs & Monitors', 'unit' => 'pieces', 'min_quantity' => 3],
                ['name' => 'Printers & Scanners', 'unit' => 'pieces', 'min_quantity' => 2],
                ['name' => 'Batteries', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Cables & Wires', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Circuit Boards', 'unit' => 'kg', 'min_quantity' => 5],
                ['name' => 'Home Appliances', 'unit' => 'pieces', 'min_quantity' => 2],
                ['name' => 'Keyboards & Mice', 'unit' => 'pieces', 'min_quantity' => 20],
                ['name' => 'Chargers & Adapters', 'unit' => 'kg', 'min_quantity' => 10],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $ewaste->id, 'is_active' => true]));
            }
        }

        // ==================== PLASTICS ====================
        if ($plastics) {
            $types = [
                ['name' => 'PET Bottles', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'HDPE Containers', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Plastic Bags', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Industrial Plastic Scraps', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Plastic Crates', 'unit' => 'pieces', 'min_quantity' => 20],
                ['name' => 'PVC Pipes', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Plastic Bottle Caps', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Yogurt Cups', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Plastic Buckets', 'unit' => 'pieces', 'min_quantity' => 10],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $plastics->id, 'is_active' => true]));
            }
        }

        // ==================== PAPER & CARDBOARD ====================
        if ($paper) {
            $types = [
                ['name' => 'Cardboard Boxes', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Office Paper', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Newspapers', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Magazines', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Books', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Tetra Pak Cartons', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Shredded Paper', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Envelopes', 'unit' => 'kg', 'min_quantity' => 10],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $paper->id, 'is_active' => true]));
            }
        }

        // ==================== METALS ====================
        if ($metals) {
            $types = [
                ['name' => 'Aluminum Cans', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Steel Scraps', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Copper Wires', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Iron Sheets', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Car Parts (Scrap Metal)', 'unit' => 'kg', 'min_quantity' => 200],
                ['name' => 'Metal Fabrication Waste', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Brass Scraps', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Metal Cans (Food)', 'unit' => 'kg', 'min_quantity' => 30],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $metals->id, 'is_active' => true]));
            }
        }

        // ==================== GLASS ====================
        if ($glass) {
            $types = [
                ['name' => 'Glass Bottles (Beer/Soda)', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Jars', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Window Glass', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Broken Glass', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Wine Bottles', 'unit' => 'pieces', 'min_quantity' => 50],
                ['name' => 'Glass Cups & Glasses', 'unit' => 'kg', 'min_quantity' => 20],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $glass->id, 'is_active' => true]));
            }
        }

        // ==================== TEXTILES & FABRICS ====================
        if ($textiles) {
            $types = [
                ['name' => 'Used Clothing', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Fabric Scraps (Tailors)', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Denim Waste', 'unit' => 'kg', 'min_quantity' => 40],
                ['name' => 'Shoes', 'unit' => 'pairs', 'min_quantity' => 50],
                ['name' => 'Mattresses', 'unit' => 'pieces', 'min_quantity' => 5],
                ['name' => 'Leather Scraps', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Cotton Waste', 'unit' => 'kg', 'min_quantity' => 30],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $textiles->id, 'is_active' => true]));
            }
        }

        // ==================== RUBBER & TIRES ====================
        if ($rubber) {
            $types = [
                ['name' => 'Vehicle Tires', 'unit' => 'pieces', 'min_quantity' => 20],
                ['name' => 'Rubber Hoses', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Rubber Mats', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'Conveyor Belts', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Inner Tubes', 'unit' => 'pieces', 'min_quantity' => 50],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $rubber->id, 'is_active' => true]));
            }
        }

        // ==================== CONSTRUCTION WASTE ====================
        if ($construction) {
            $types = [
                ['name' => 'Concrete Blocks', 'unit' => 'kg', 'min_quantity' => 500],
                ['name' => 'Wood Scraps', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Drywall', 'unit' => 'kg', 'min_quantity' => 200],
                ['name' => 'Ceramics & Tiles', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Roofing Materials', 'unit' => 'kg', 'min_quantity' => 150],
                ['name' => 'Bricks', 'unit' => 'pieces', 'min_quantity' => 200],
                ['name' => 'Insulation Materials', 'unit' => 'kg', 'min_quantity' => 50],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $construction->id, 'is_active' => true]));
            }
        }

        // ==================== HAZARDOUS WASTE ====================
        if ($hazardous) {
            $types = [
                ['name' => 'Paint & Solvents', 'unit' => 'litres', 'min_quantity' => 20],
                ['name' => 'Used Oil', 'unit' => 'litres', 'min_quantity' => 50],
                ['name' => 'Chemical Waste', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Pesticides', 'unit' => 'kg', 'min_quantity' => 5],
                ['name' => 'Aerosol Cans', 'unit' => 'pieces', 'min_quantity' => 50],
                ['name' => 'Industrial Chemicals', 'unit' => 'litres', 'min_quantity' => 20],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $hazardous->id, 'is_active' => true]));
            }
        }

        // ==================== WOOD & BIOMASS ====================
        if ($wood) {
            $types = [
                ['name' => 'Pallet Wood', 'unit' => 'pieces', 'min_quantity' => 20],
                ['name' => 'Sawdust', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Tree Trimmings', 'unit' => 'kg', 'min_quantity' => 200],
                ['name' => 'Bamboo Scraps', 'unit' => 'kg', 'min_quantity' => 50],
                ['name' => 'Untreated Timber', 'unit' => 'kg', 'min_quantity' => 100],
                ['name' => 'Wood Chips', 'unit' => 'kg', 'min_quantity' => 150],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $wood->id, 'is_active' => true]));
            }
        }

        // ==================== BIO-MEDICAL WASTE ====================
        if ($biomedical) {
            $types = [
                ['name' => 'Hospital Plastics', 'unit' => 'kg', 'min_quantity' => 20],
                ['name' => 'Syringes & Needles', 'unit' => 'pieces', 'min_quantity' => 500],
                ['name' => 'Laboratory Glassware', 'unit' => 'kg', 'min_quantity' => 10],
                ['name' => 'Pharmaceutical Waste', 'unit' => 'kg', 'min_quantity' => 5],
                ['name' => 'Gloves & Masks', 'unit' => 'kg', 'min_quantity' => 30],
                ['name' => 'IV Bags & Tubes', 'unit' => 'kg', 'min_quantity' => 20],
            ];
            foreach ($types as $type) {
                WasteType::create(array_merge($type, ['category_id' => $biomedical->id, 'is_active' => true]));
            }
        }

        $this->command->info('✅ Waste types seeded for all 12 categories!');
    }
}