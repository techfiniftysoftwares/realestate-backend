<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use App\Models\Location;
use App\Models\PropertyUseCategory;
use App\Models\PropertyStyle;
use App\Models\Amenity;

class FilterController extends Controller
{
    /**
     * Get all property types for filters
     */
    public function getPropertyTypes()
    {
        try {
            $types = PropertyType::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'icon']);

            return successResponse(
                'Property types retrieved successfully',
                $types
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve property types', $e->getMessage());
        }
    }

    /**
     * Get all locations for filters
     */
    public function getLocations()
    {
        try {
            $locations = Location::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'city', 'county']);

            return successResponse(
                'Locations retrieved successfully',
                $locations
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve locations', $e->getMessage());
        }
    }

    /**
     * Get all use categories for filters
     */
    public function getUseCategories()
    {
        try {
            $categories = PropertyUseCategory::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'description']);

            return successResponse(
                'Use categories retrieved successfully',
                $categories
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve use categories', $e->getMessage());
        }
    }

    /**
     * Get all property styles for filters
     */
    public function getStyles()
    {
        try {
            $styles = PropertyStyle::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'description']);

            return successResponse(
                'Property styles retrieved successfully',
                $styles
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve property styles', $e->getMessage());
        }
    }

    /**
     * Get all amenities for filters
     */
    public function getAmenities()
    {
        try {
            $amenities = Amenity::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'icon']);

            return successResponse(
                'Amenities retrieved successfully',
                $amenities
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve amenities', $e->getMessage());
        }
    }

    /**
     * Get all filter options in one request
     */
    public function getAllFilters()
    {
        try {
            $filters = [
                'types' => PropertyType::active()->ordered()->get(['id', 'name', 'slug', 'icon']),
                'locations' => Location::active()->ordered()->get(['id', 'name', 'slug', 'city', 'county']),
                'useCategories' => PropertyUseCategory::active()->ordered()->get(['id', 'name', 'slug']),
                'styles' => PropertyStyle::active()->ordered()->get(['id', 'name', 'slug']),
                'amenities' => Amenity::active()->ordered()->get(['id', 'name', 'slug', 'icon']),
                'budgetRanges' => $this->getBudgetRanges(),
                'bedrooms' => $this->getBedroomOptions(),
                'bathrooms' => $this->getBathroomOptions(),
            ];

            return successResponse(
                'All filters retrieved successfully',
                $filters
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve filters', $e->getMessage());
        }
    }

    /**
     * Get budget range options
     */
    private function getBudgetRanges()
    {
        return [
            ['value' => '0-500000', 'label' => 'Under KES 500K'],
            ['value' => '500000-1000000', 'label' => 'KES 500K - 1M'],
            ['value' => '1000000-2000000', 'label' => 'KES 1M - 2M'],
            ['value' => '2000000-5000000', 'label' => 'KES 2M - 5M'],
            ['value' => '5000000-10000000', 'label' => 'KES 5M - 10M'],
            ['value' => '10000000-20000000', 'label' => 'KES 10M - 20M'],
            ['value' => '20000000-999999999', 'label' => 'Above KES 20M'],
        ];
    }

    /**
     * Get bedroom filter options
     */
    private function getBedroomOptions()
    {
        return [
            ['value' => 'any', 'label' => 'Any'],
            ['value' => '1', 'label' => '1+'],
            ['value' => '2', 'label' => '2+'],
            ['value' => '3', 'label' => '3+'],
            ['value' => '4+', 'label' => '4+'],
        ];
    }

    /**
     * Get bathroom filter options
     */
    private function getBathroomOptions()
    {
        return [
            ['value' => 'any', 'label' => 'Any'],
            ['value' => '1', 'label' => '1+'],
            ['value' => '2', 'label' => '2+'],
            ['value' => '3+', 'label' => '3+'],
        ];
    }
}
