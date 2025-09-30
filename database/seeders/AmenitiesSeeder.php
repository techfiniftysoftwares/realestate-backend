<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitiesSeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Pool', 'icon' => '🏊', 'order' => 1],
            ['name' => 'Ocean View', 'icon' => '🌊', 'order' => 2],
            ['name' => 'Garage', 'icon' => '🚗', 'order' => 3],
            ['name' => 'Garden', 'icon' => '🌻', 'order' => 4],
            ['name' => 'City View', 'icon' => '🌆', 'order' => 5],
            ['name' => 'Gym', 'icon' => '💪', 'order' => 6],
            ['name' => 'Concierge', 'icon' => '🛎️', 'order' => 7],
            ['name' => 'Rooftop', 'icon' => '🏙️', 'order' => 8],
            ['name' => 'Backyard', 'icon' => '🏡', 'order' => 9],
            ['name' => 'Fireplace', 'icon' => '🔥', 'order' => 10],
            ['name' => 'Patio', 'icon' => '🪑', 'order' => 11],
            ['name' => 'Water Access', 'icon' => '💧', 'order' => 12],
            ['name' => 'Road Access', 'icon' => '🛣️', 'order' => 13],
            ['name' => 'Fertile Soil', 'icon' => '🌱', 'order' => 14],
            ['name' => 'Fenced', 'icon' => '🚧', 'order' => 15],
            ['name' => 'Main Road Frontage', 'icon' => '🛤️', 'order' => 16],
            ['name' => 'Utilities Available', 'icon' => '⚡', 'order' => 17],
            ['name' => 'Commercial Zone', 'icon' => '🏢', 'order' => 18],
            ['name' => 'High Traffic', 'icon' => '🚦', 'order' => 19],
            ['name' => 'Gated Community', 'icon' => '🚪', 'order' => 20],
            ['name' => 'Underground Utilities', 'icon' => '⚙️', 'order' => 21],
            ['name' => 'Landscaped', 'icon' => '🌳', 'order' => 22],
            ['name' => 'Security', 'icon' => '🛡️', 'order' => 23],
            ['name' => 'Balcony', 'icon' => '🏞️', 'order' => 24],
            ['name' => 'Walk-in Closet', 'icon' => '👔', 'order' => 25],
            ['name' => 'Central AC', 'icon' => '❄️', 'order' => 26],
            ['name' => 'Solar Panels', 'icon' => '☀️', 'order' => 27],
            ['name' => 'Smart Home', 'icon' => '🏠', 'order' => 28],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
