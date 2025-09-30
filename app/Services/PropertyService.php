<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyService
{
    /**
     * Create a new property with all relationships
     */
    public function createProperty(array $data): Property
    {
        DB::beginTransaction();

        try {
            // Extract amenities and images before creating property
            $amenities = $data['amenities'] ?? [];
            $images = $data['images'] ?? [];
            unset($data['amenities'], $data['images']);

            // Create property
            $property = Property::create($data);

            // Attach amenities
            if (!empty($amenities)) {
                $property->amenities()->sync($amenities);
            }

            // Handle images
            if (!empty($images)) {
                foreach ($images as $image) {
                    $property->addMedia($image)
                        ->toMediaCollection('property_images');
                }
            }

            DB::commit();

            return $property->load(['propertyType', 'location', 'amenities']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update property with all relationships
     */
    public function updateProperty(Property $property, array $data): Property
    {
        DB::beginTransaction();

        try {
            // Extract relationships
            $amenities = $data['amenities'] ?? null;
            $images = $data['images'] ?? null;
            unset($data['amenities'], $data['images']);

            // Update property
            $property->update($data);

            // Update amenities if provided
            if ($amenities !== null) {
                $property->amenities()->sync($amenities);
            }

            // Handle new images
            if ($images) {
                foreach ($images as $image) {
                    $property->addMedia($image)
                        ->toMediaCollection('property_images');
                }
            }

            DB::commit();

            return $property->fresh(['propertyType', 'location', 'amenities']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete property and all relationships
     */
    public function deleteProperty(Property $property): bool
    {
        DB::beginTransaction();

        try {
            // Delete all media
            $property->clearMediaCollection('property_images');
            $property->clearMediaCollection('featured_image');

            // Soft delete property (cascades will handle the rest)
            $property->delete();

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property deletion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get similar properties based on type and location
     */
    public function getSimilarProperties(Property $property, int $limit = 4)
    {
        return Property::where('id', '!=', $property->id)
            ->where('status', 'available')
            ->where(function ($query) use ($property) {
                $query->where('property_type_id', $property->property_type_id)
                    ->orWhere('location_id', $property->location_id);
            })
            ->whereBetween('price', [
                $property->price * 0.7,
                $property->price * 1.3
            ])
            ->with(['propertyType', 'location', 'amenities'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get property statistics
     */
    public function getPropertyStats(): array
    {
        return [
            'total' => Property::count(),
            'available' => Property::where('status', 'available')->count(),
            'sold' => Property::where('status', 'sold')->count(),
            'pending' => Property::where('status', 'pending')->count(),
            'featured' => Property::where('is_featured', true)->count(),
            'best_deals' => Property::where('is_best_deal', true)->count(),
            'for_sale' => Property::where('listing_type', 'sale')->count(),
            'for_rent' => Property::where('listing_type', 'rent')->count(),
            'avg_price' => Property::where('status', 'available')->avg('price'),
            'most_viewed' => Property::orderBy('view_count', 'desc')->first(),
        ];
    }

    /**
     * Update property images
     */
    public function updateImages(Property $property, array $images): Property
    {
        foreach ($images as $image) {
            $property->addMedia($image)
                ->toMediaCollection('property_images');
        }

        return $property;
    }

    /**
     * Remove specific image
     */
    public function removeImage(Property $property, int $mediaId): bool
    {
        $media = $property->getMedia('property_images')
            ->where('id', $mediaId)
            ->first();

        if ($media) {
            $media->delete();
            return true;
        }

        return false;
    }
}
