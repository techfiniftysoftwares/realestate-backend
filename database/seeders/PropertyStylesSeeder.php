<?php

namespace Database\Seeders;

use App\Models\PropertyStyle;
use Illuminate\Database\Seeder;

class PropertyStylesSeeder extends Seeder
{
    public function run(): void
    {
        $styles = [
            ['name' => 'Modern', 'description' => 'Contemporary design with clean lines', 'order' => 1],
            ['name' => 'Contemporary', 'description' => 'Current design trends', 'order' => 2],
            ['name' => 'Traditional', 'description' => 'Classic architectural style', 'order' => 3],
            ['name' => 'Colonial', 'description' => 'Historical colonial architecture', 'order' => 4],
            ['name' => 'Mediterranean', 'description' => 'Mediterranean-inspired design', 'order' => 5],
            ['name' => 'Industrial', 'description' => 'Urban industrial aesthetic', 'order' => 6],
            ['name' => 'Minimalist', 'description' => 'Simple and functional design', 'order' => 7],
        ];

        foreach ($styles as $style) {
            PropertyStyle::create($style);
        }
    }
}
