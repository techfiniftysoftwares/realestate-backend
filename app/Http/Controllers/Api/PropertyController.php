<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties with advanced filters
     */
    public function index(Request $request)
    {
        try {
            $query = Property::query()
                ->with(['propertyType', 'location', 'amenities', 'propertyUseCategory', 'propertyStyle', 'agent'])
                ->available();

            // Apply filters
            $this->applyFilters($query, $request);

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['created_at', 'price', 'bedrooms', 'bathrooms', 'area', 'view_count', 'title'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $properties = $query->paginate($perPage);

            return paginatedResponse(
                PropertyResource::collection($properties),
                'Properties retrieved successfully'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve properties', $e->getMessage());
        }
    }

    /**
     * Get featured properties
     */
    public function featured()
    {
        try {
            $properties = Property::featured()
                ->available()
                ->with(['propertyType', 'location', 'amenities', 'agent'])
                ->latest()
                ->limit(6)
                ->get();

            return successResponse(
                'Featured properties retrieved successfully',
                PropertyResource::collection($properties)
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve featured properties', $e->getMessage());
        }
    }

    /**
     * Get best deal properties
     */
    public function bestDeals()
    {
        try {
            $properties = Property::bestDeals()
                ->available()
                ->with(['propertyType', 'location', 'amenities', 'agent'])
                ->latest()
                ->limit(6)
                ->get();

            return successResponse(
                'Best deals retrieved successfully',
                PropertyResource::collection($properties)
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve best deals', $e->getMessage());
        }
    }

    /**
     * Display the specified property
     */
    public function show($slug)
    {
        try {
            $property = Property::where('slug', $slug)
                ->with(['propertyType', 'location', 'amenities', 'propertyUseCategory', 'propertyStyle', 'agent'])
                ->first();

            if (!$property) {
                return notFoundResponse('Property not found');
            }

            // Increment view count
            $property->incrementViewCount();

            return successResponse(
                'Property retrieved successfully',
                new PropertyResource($property)
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve property', $e->getMessage());
        }
    }

    /**
     * Store a newly created property
     */
    public function store(StorePropertyRequest $request)
    {
        DB::beginTransaction();
        try {
            // Create property
            $property = Property::create($request->except('amenities', 'images'));

            // Attach amenities
            if ($request->filled('amenities')) {
                $property->amenities()->sync($request->amenities);
            }

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $property->addMedia($image)->toMediaCollection('property_images');
                }
            }

            DB::commit();

            $property->load(['propertyType', 'location', 'amenities', 'propertyUseCategory', 'propertyStyle']);

            return createdResponse(
                new PropertyResource($property),
                'Property created successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return serverErrorResponse('Failed to create property', $e->getMessage());
        }
    }

    /**
     * Update the specified property
     */
    public function update(UpdatePropertyRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $property = Property::find($id);

            if (!$property) {
                return notFoundResponse('Property not found');
            }

            // Update property
            $property->update($request->except('amenities', 'images'));

            // Sync amenities
            if ($request->has('amenities')) {
                $property->amenities()->sync($request->amenities);
            }

            // Handle new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $property->addMedia($image)->toMediaCollection('property_images');
                }
            }

            DB::commit();

            $property->load(['propertyType', 'location', 'amenities', 'propertyUseCategory', 'propertyStyle']);

            return updatedResponse(
                new PropertyResource($property),
                'Property updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return serverErrorResponse('Failed to update property', $e->getMessage());
        }
    }

    /**
     * Remove the specified property
     */
    public function destroy($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return notFoundResponse('Property not found');
            }

            $property->delete();

            return deleteResponse('Property deleted successfully');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to delete property', $e->getMessage());
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Type filter
        if ($request->filled('type_id')) {
            $query->byType($request->type_id);
        }

        // Location filter
        if ($request->filled('location_id')) {
            $query->byLocation($request->location_id);
        }

        // Use Category filter
        if ($request->filled('use_category_id')) {
            $query->where('property_use_category_id', $request->use_category_id);
        }

        // Style filter
        if ($request->filled('style_id')) {
            $query->where('property_style_id', $request->style_id);
        }

        // Price range filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->get('min_price', 0);
            $maxPrice = $request->get('max_price', PHP_FLOAT_MAX);
            $query->priceRange($minPrice, $maxPrice);
        }

        // Bedrooms filter
        if ($request->filled('bedrooms')) {
            $query->minBedrooms($request->bedrooms);
        }

        // Bathrooms filter
        if ($request->filled('bathrooms')) {
            $query->minBathrooms($request->bathrooms);
        }

        // Amenities filter
        if ($request->filled('amenities') && is_array($request->amenities)) {
            foreach ($request->amenities as $amenityId) {
                $query->whereHas('amenities', function ($q) use ($amenityId) {
                    $q->where('amenities.id', $amenityId);
                });
            }
        }

        // Featured filter
        if ($request->filled('featured') && $request->featured) {
            $query->featured();
        }

        // Best deals filter
        if ($request->filled('best_deal') && $request->best_deal) {
            $query->bestDeals();
        }

        // Listing type filter
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        return $query;
    }
}
