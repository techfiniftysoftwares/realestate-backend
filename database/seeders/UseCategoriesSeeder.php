<?php

namespace Database\Seeders;

use App\Models\PropertyUseCategory;
use Illuminate\Database\Seeder;

class UseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Family House', 'description' => 'Ideal for families', 'order' => 1],
            ['name' => 'Commercial', 'description' => 'For business purposes', 'order' => 2],
            ['name' => 'Rental Property', 'description' => 'Investment rental property', 'order' => 3],
            ['name' => 'Land & Agricultural Properties', 'description' => 'Farming and agricultural use', 'order' => 4],
            ['name' => 'Student Housing', 'description' => 'Accommodation for students', 'order' => 5],
            ['name' => 'Vacation Home', 'description' => 'Holiday and leisure property', 'order' => 6],
            ['name' => 'Industrial Properties', 'description' => 'Manufacturing and warehousing', 'order' => 7],
        ];

        foreach ($categories as $category) {
            PropertyUseCategory::create($category);
        }
    }
}
