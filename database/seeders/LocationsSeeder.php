<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Westlands', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 1],
            ['name' => 'Karen', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 2],
            ['name' => 'Kilimani', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 3],
            ['name' => 'Lavington', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 4],
            ['name' => 'Runda', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 5],
            ['name' => 'Muthaiga', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 6],
            ['name' => 'Kileleshwa', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 7],
            ['name' => 'Thika Road', 'city' => 'Nairobi', 'county' => 'Nairobi County', 'order' => 8],
            ['name' => 'Kiambu', 'city' => 'Kiambu', 'county' => 'Kiambu County', 'order' => 9],
            ['name' => 'Ruaka', 'city' => 'Kiambu', 'county' => 'Kiambu County', 'order' => 10],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
