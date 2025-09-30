<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use App\Models\PropertyUseCategory;
use App\Models\PropertyStyle;
use App\Models\Amenity;
use Illuminate\Database\Seeder;

class PropertiesSeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            [
                'title' => 'Executive Villa in Karen',
                'description' => 'Stunning contemporary villa with panoramic views of Ngong Hills',
                'property_type_id' => PropertyType::where('name', 'Villa')->first()->id,
                'location_id' => Location::where('name', 'Karen')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Family House')->first()->id,
                'property_style_id' => PropertyStyle::where('name', 'Contemporary')->first()->id,
                'price' => 85000000,
                'bedrooms' => 5,
                'bathrooms' => 4,
                'area' => 4500,
                'lot_size' => 8000,
                'year_built' => 2020,
                'garage_spaces' => 3,
                'is_featured' => true,
                'is_best_deal' => true,
                'status' => 'available',
                'listing_type' => 'sale',
                'neighborhood_ratings' => [
                    'schools' => 9,
                    'restaurants' => 8,
                    'transit' => 6,
                    'shopping' => 7,
                ],
                'amenities' => ['Pool', 'Ocean View', 'Garage', 'Garden'],
            ],
            [
                'title' => 'Westlands Luxury Apartment',
                'description' => 'Modern apartment in the heart of Nairobi',
                'property_type_id' => PropertyType::where('name', 'Apartment')->first()->id,
                'location_id' => Location::where('name', 'Westlands')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Rental Property')->first()->id,
                'property_style_id' => PropertyStyle::where('name', 'Modern')->first()->id,
                'price' => 25000000,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area' => 1200,
                'lot_size' => 0,
                'year_built' => 2018,
                'garage_spaces' => 1,
                'is_featured' => true,
                'is_best_deal' => false,
                'status' => 'available',
                'listing_type' => 'sale',
                'neighborhood_ratings' => [
                    'schools' => 8,
                    'restaurants' => 10,
                    'transit' => 10,
                    'shopping' => 9,
                ],
                'amenities' => ['City View', 'Gym', 'Concierge', 'Rooftop'],
            ],
            [
                'title' => 'Kilimani Family Home',
                'description' => 'Perfect family home with spacious compound',
                'property_type_id' => PropertyType::where('name', 'House')->first()->id,
                'location_id' => Location::where('name', 'Kilimani')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Family House')->first()->id,
                'property_style_id' => PropertyStyle::where('name', 'Traditional')->first()->id,
                'price' => 45000000,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area' => 2800,
                'lot_size' => 6000,
                'year_built' => 2015,
                'garage_spaces' => 2,
                'is_featured' => false,
                'is_best_deal' => true,
                'status' => 'available',
                'listing_type' => 'sale',
                'amenities' => ['Backyard', 'Garage', 'Fireplace', 'Patio'],
            ],
            [
                'title' => 'Prime Agricultural Land - Kiambu',
                'description' => 'Fertile agricultural land perfect for farming or development, with water access and good road connectivity',
                'property_type_id' => PropertyType::where('name', 'Land')->first()->id,
                'location_id' => Location::where('name', 'Kiambu')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Land & Agricultural Properties')->first()->id,
                'property_style_id' => null,
                'price' => 12000000,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'area' => 50000,
                'lot_size' => 50000,
                'year_built' => null,
                'garage_spaces' => 0,
                'is_featured' => false,
                'is_best_deal' => true,
                'status' => 'available',
                'listing_type' => 'sale',
                'neighborhood_ratings' => [
                    'schools' => 6,
                    'restaurants' => 4,
                    'transit' => 5,
                    'shopping' => 5,
                ],
                'amenities' => ['Water Access', 'Road Access', 'Fertile Soil', 'Fenced'],
            ],
            [
                'title' => 'Commercial Plot - Thika Road',
                'description' => 'Strategic commercial plot along busy Thika Road, ideal for retail or office development',
                'property_type_id' => PropertyType::where('name', 'Commercial Land')->first()->id,
                'location_id' => Location::where('name', 'Thika Road')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Commercial')->first()->id,
                'property_style_id' => null,
                'price' => 35000000,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'area' => 8000,
                'lot_size' => 8000,
                'year_built' => null,
                'garage_spaces' => 0,
                'is_featured' => true,
                'is_best_deal' => false,
                'status' => 'available',
                'listing_type' => 'sale',
                'neighborhood_ratings' => [
                    'schools' => 7,
                    'restaurants' => 8,
                    'transit' => 9,
                    'shopping' => 8,
                ],
                'amenities' => ['Main Road Frontage', 'Utilities Available', 'Commercial Zone', 'High Traffic'],
            ],
            [
                'title' => 'Residential Plot - Runda',
                'description' => 'Premium residential plot in exclusive Runda estate, ready for construction of luxury home',
                'property_type_id' => PropertyType::where('name', 'Residential Land')->first()->id,
                'location_id' => Location::where('name', 'Runda')->first()->id,
                'property_use_category_id' => PropertyUseCategory::where('name', 'Family House')->first()->id,
                'property_style_id' => null,
                'price' => 28000000,
                'bedrooms' => 0,
                'bathrooms' => 0,
                'area' => 12000,
                'lot_size' => 12000,
                'year_built' => null,
                'garage_spaces' => 0,
                'is_featured' => false,
                'is_best_deal' => false,
                'status' => 'available',
                'listing_type' => 'sale',
                'amenities' => ['Gated Community', 'Underground Utilities', 'Landscaped', 'Security'],
            ],
        ];

        foreach ($properties as $propertyData) {
            $amenityNames = $propertyData['amenities'];
            unset($propertyData['amenities']);

            $property = Property::create($propertyData);

            // Attach amenities
            $amenityIds = Amenity::whereIn('name', $amenityNames)->pluck('id');
            $property->amenities()->attach($amenityIds);
        }
    }
}
