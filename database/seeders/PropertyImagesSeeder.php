<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertyImagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Adding images to properties...');

        $propertyImages = [
            // Executive Villa in Karen (ID: 1)
            'executive-villa-in-karen' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
            ],
            // Westlands Luxury Apartment (ID: 2)
            'westlands-luxury-apartment' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
            ],
            // Kilimani Family Home (ID: 3)
            'kilimani-family-home' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
                    'https://images.unsplash.com/photo-1448630360428-65456885c650?w=800',
                    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
            ],
            // Prime Agricultural Land - Kiambu (ID: 4)
            'prime-agricultural-land-kiambu' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                    'https://images.unsplash.com/photo-1574263867128-a3d5c1b1deac?w=800',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
            ],
            // Commercial Plot - Thika Road (ID: 5)
            'commercial-plot-thika-road' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
            ],
            // Residential Plot - Runda (ID: 6)
            'residential-plot-runda' => [
                'property_images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                    'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800',
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                ],
                'featured_image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
            ],
        ];

        foreach ($propertyImages as $slug => $images) {
            $property = Property::where('slug', 'LIKE', $slug . '%')->first();

            if (!$property) {
                $this->command->warn("Property not found for slug pattern: {$slug}");
                continue;
            }

            $this->command->info("Adding images to: {$property->title}");

            try {
                // Add property images
                foreach ($images['property_images'] as $index => $imageUrl) {
                    $property->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('property_images');

                    $this->command->info("  ✓ Added image " . ($index + 1));
                }

                // Add featured image
                if (isset($images['featured_image'])) {
                    $property->addMediaFromUrl($images['featured_image'])
                        ->toMediaCollection('featured_image');

                    $this->command->info("  ✓ Added featured image");
                }

            } catch (\Exception $e) {
                $this->command->error("  ✗ Failed to add images: " . $e->getMessage());
            }
        }

        $this->command->info('✓ Image seeding completed!');
    }
}
