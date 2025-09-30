<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Villa', 'icon' => '🏰', 'description' => 'Luxury standalone property', 'order' => 1],
            ['name' => 'Apartment', 'icon' => '🏢', 'description' => 'Multi-unit residential building', 'order' => 2],
            ['name' => 'House', 'icon' => '🏠', 'description' => 'Single-family residential property', 'order' => 3],
            ['name' => 'Townhouse', 'icon' => '🏘️', 'description' => 'Multi-floor home sharing walls', 'order' => 4],
            ['name' => 'Penthouse', 'icon' => '🌆', 'description' => 'Luxury top-floor apartment', 'order' => 5],
            ['name' => 'Loft', 'icon' => '🏗️', 'description' => 'Open-plan converted space', 'order' => 6],
            ['name' => 'Land', 'icon' => '🌳', 'description' => 'Undeveloped property', 'order' => 7],
            ['name' => 'Commercial Land', 'icon' => '🏗️', 'description' => 'Land for commercial development', 'order' => 8],
            ['name' => 'Residential Land', 'icon' => '🏡', 'description' => 'Land for residential development', 'order' => 9],
        ];

        foreach ($types as $type) {
            PropertyType::create($type);
        }
    }
}
