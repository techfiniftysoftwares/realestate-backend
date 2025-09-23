<?php

// app/Http/Controllers/Api/PropertyController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    // PUBLIC METHODS (No authentication required)

    /**
     * Get properties list (Public)
     * GET /api/v1/public/properties
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');

            // Validate sort field
            $allowedSortFields = ['title', 'price', 'created_at', 'view_count', 'bedrooms', 'bathrooms'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            $query = Property::with('media')->active();

            // Apply filters
            $this->applyPublicFilters($query, $request);

            // Apply sorting
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');

            $properties = $query->paginate($perPage);

            if ($properties->isEmpty()) {
                return successResponse('No properties found matching your criteria', [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ]
                ]);
            }

            // Transform properties
            $transformedProperties = $properties->through(function ($property) {
                return $this->transformProperty($property);
            });

            return paginatedResponse($transformedProperties, 'Properties retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve properties', $e->getMessage());
        }
    }

    /**
     * Get single property by slug (Public)
     * GET /api/v1/public/properties/{slug}
     */
    public function show($slug, Request $request)
    {
        try {
            $property = Property::with('media')
                             ->where('slug', $slug)
                             ->where('status', 'active')
                             ->first();

            if (!$property) {
                return notFoundResponse('Property not found');
            }

            // Track property view (anonymous)
            $property->trackView($request);

            // Get similar properties
            $similarProperties = Property::where('id', '!=', $property->id)
                                       ->where('type', $property->type)
                                       ->where('city', $property->city)
                                       ->active()
                                       ->with('media')
                                       ->limit(4)
                                       ->get()
                                       ->map(function($prop) {
                                           return [
                                               'id' => $prop->id,
                                               'title' => $prop->title,
                                               'slug' => $prop->slug,
                                               'price' => $prop->formatted_price,
                                               'featured_image' => $prop->featured_image_url,
                                               'bedrooms' => $prop->bedrooms,
                                               'bathrooms' => $prop->bathrooms,
                                               'location' => $prop->city . ', ' . $prop->county,
                                           ];
                                       });

            $transformedProperty = $this->transformProperty($property);
            $transformedProperty['similar_properties'] = $similarProperties;

            return successResponse('Property retrieved successfully', $transformedProperty);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve property', $e->getMessage());
        }
    }

    /**
     * Get featured properties (Public)
     * GET /api/v1/public/properties/featured
     */
    public function featured()
    {
        try {
            $properties = Property::with('media')
                                 ->featured()
                                 ->active()
                                 ->limit(6)
                                 ->get()
                                 ->map(function($property) {
                                     return [
                                         'id' => $property->id,
                                         'title' => $property->title,
                                         'slug' => $property->slug,
                                         'price' => $property->formatted_price,
                                         'featured_image' => $property->featured_image_url,
                                         'bedrooms' => $property->bedrooms,
                                         'bathrooms' => $property->bathrooms,
                                         'location' => $property->city . ', ' . $property->county,
                                         'type' => $property->type,
                                         'listing_type' => $property->listing_type,
                                     ];
                                 });

            return successResponse('Featured properties retrieved successfully', $properties);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve featured properties', $e->getMessage());
        }
    }

    /**
     * Search properties (Public)
     * GET /api/v1/public/properties/search
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:100',
            ]);

            if ($validator->fails()) {
                return validationErrorResponse($validator->errors());
            }

            $search = $request->q;
            $properties = Property::with('media')
                                ->active()
                                ->where(function($query) use ($search) {
                                    $query->where('title', 'like', '%' . $search . '%')
                                          ->orWhere('address', 'like', '%' . $search . '%')
                                          ->orWhere('city', 'like', '%' . $search . '%')
                                          ->orWhere('county', 'like', '%' . $search . '%');
                                })
                                ->limit(10)
                                ->get()
                                ->map(function($property) {
                                    return [
                                        'id' => $property->id,
                                        'title' => $property->title,
                                        'slug' => $property->slug,
                                        'address' => $property->address,
                                        'city' => $property->city,
                                        'county' => $property->county,
                                        'price' => $property->formatted_price,
                                        'featured_image' => $property->featured_image_url,
                                        'type' => $property->type,
                                        'bedrooms' => $property->bedrooms,
                                        'bathrooms' => $property->bathrooms,
                                    ];
                                });

            return successResponse('Search results retrieved successfully', $properties);

        } catch (\Exception $e) {
            return queryErrorResponse('Search failed', $e->getMessage());
        }
    }

    // ADMIN METHODS (Authentication required)

    /**
     * Get all properties for admin (Admin)
     * GET /api/v1/admin/properties
     */
    public function adminIndex(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');

            $allowedSortFields = ['title', 'price', 'created_at', 'view_count', 'status', 'type'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            $query = Property::with('media');

            // Apply admin filters
            $this->applyAdminFilters($query, $request);

            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');

            $properties = $query->paginate($perPage);

            $transformedProperties = $properties->through(function ($property) {
                return $this->transformPropertySummary($property);
            });

            return paginatedResponse($transformedProperties, 'Properties retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve properties', $e->getMessage());
        }
    }

    /**
     * Get single property for admin (Admin)
     * GET /api/v1/admin/properties/{id}
     */
    public function adminShow(Property $property)
    {
        try {
            $property->load('media');
            return successResponse('Property retrieved successfully', $this->transformPropertyDetail($property));
        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve property', $e->getMessage());
        }
    }

    /**
     * Create new property (Admin)
     * POST /api/v1/admin/properties
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:residential,commercial,land',
            'listing_type' => 'required|in:sale,rent,lease',
            'price' => 'required|numeric|min:0',
            'address' => 'required|string',
            'city' => 'required|string',
            'county' => 'required|string',
            'country' => 'sometimes|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'square_footage' => 'nullable|numeric|min:0',
            'lot_size' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking_spaces' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
            'features' => 'nullable|array',
            'virtual_tour_url' => 'nullable|url',
            'is_featured' => 'sometimes|boolean',
            'status' => 'sometimes|in:active,sold,pending,draft',
            'images.*' => 'nullable|image|max:5120',
            'featured_image' => 'nullable|image|max:5120',
            'documents.*' => 'nullable|mimes:pdf,doc,docx|max:10240',
            'floor_plans.*' => 'nullable|mimes:pdf,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            // Remove file uploads from property data
            $propertyData = collect($validated)->except([
                'images', 'featured_image', 'documents', 'floor_plans'
            ])->toArray();

            $property = Property::create($propertyData);

            // Handle file uploads
            if ($request->hasFile('featured_image')) {
                $property->addMediaFromRequest('featured_image')
                        ->toMediaCollection('featured_image');
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $property->addMedia($image)
                            ->toMediaCollection('images');
                }
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $property->addMedia($document)
                            ->toMediaCollection('documents');
                }
            }

            if ($request->hasFile('floor_plans')) {
                foreach ($request->file('floor_plans') as $plan) {
                    $property->addMedia($plan)
                            ->toMediaCollection('floor_plans');
                }
            }

            $property->load('media');

            DB::commit();

            return createdResponse(
                $this->transformPropertyDetail($property),
                'Property created successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to create property', $e->getMessage());
        }
    }

    /**
     * Update property (Admin)
     * PUT /api/v1/admin/properties/{id}
     */
    public function update(Request $request, Property $property)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|in:residential,commercial,land',
            'status' => 'sometimes|in:active,sold,pending,draft',
            'listing_type' => 'sometimes|in:sale,rent,lease',
            'price' => 'sometimes|numeric|min:0',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'county' => 'sometimes|string',
            'country' => 'sometimes|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'square_footage' => 'nullable|numeric|min:0',
            'lot_size' => 'nullable|numeric|min:0',
            'year_built' => 'nullable|integer|min:1800|max:' . (date('Y') + 2),
            'parking_spaces' => 'nullable|integer|min:0',
            'amenities' => 'nullable|array',
            'features' => 'nullable|array',
            'virtual_tour_url' => 'nullable|url',
            'is_featured' => 'sometimes|boolean',
            'images.*' => 'nullable|image|max:5120',
            'featured_image' => 'nullable|image|max:5120',
            'documents.*' => 'nullable|mimes:pdf,doc,docx|max:10240',
            'floor_plans.*' => 'nullable|mimes:pdf,jpeg,png|max:10240',
            'remove_images' => 'nullable|array',
            'remove_documents' => 'nullable|array',
            'remove_floor_plans' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $validated = $validator->validated();

            // Remove file-related fields from property update data
            $propertyData = collect($validated)->except([
                'images', 'featured_image', 'documents', 'floor_plans',
                'remove_images', 'remove_documents', 'remove_floor_plans'
            ])->toArray();

            $property->update($propertyData);

            // Handle media removals
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $mediaId) {
                    $property->deleteMedia($mediaId);
                }
            }

            if ($request->has('remove_documents')) {
                foreach ($request->remove_documents as $mediaId) {
                    $property->deleteMedia($mediaId);
                }
            }

            if ($request->has('remove_floor_plans')) {
                foreach ($request->remove_floor_plans as $mediaId) {
                    $property->deleteMedia($mediaId);
                }
            }

            // Handle new file uploads
            if ($request->hasFile('featured_image')) {
                $property->clearMediaCollection('featured_image');
                $property->addMediaFromRequest('featured_image')
                        ->toMediaCollection('featured_image');
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $property->addMedia($image)
                            ->toMediaCollection('images');
                }
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $property->addMedia($document)
                            ->toMediaCollection('documents');
                }
            }

            if ($request->hasFile('floor_plans')) {
                foreach ($request->file('floor_plans') as $plan) {
                    $property->addMedia($plan)
                            ->toMediaCollection('floor_plans');
                }
            }

            $property->load('media');

            DB::commit();

            return updatedResponse(
                $this->transformPropertyDetail($property),
                'Property updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to update property', $e->getMessage());
        }
    }

    /**
     * Delete property (Admin)
     * DELETE /api/v1/admin/properties/{id}
     */
    public function destroy(Property $property)
    {
        try {
            DB::beginTransaction();

            // Delete all media files
            $property->clearMediaCollection();
            $property->delete();

            DB::commit();

            return deleteResponse('Property deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return queryErrorResponse('Failed to delete property', $e->getMessage());
        }
    }

    /**
     * Toggle featured status (Admin)
     * POST /api/v1/admin/properties/{id}/toggle-featured
     */
    public function toggleFeatured(Property $property)
    {
        try {
            $property->update(['is_featured' => !$property->is_featured]);

            $status = $property->is_featured ? 'featured' : 'unfeatured';
            return successResponse("Property {$status} successfully", [
                'id' => $property->id,
                'title' => $property->title,
                'is_featured' => $property->is_featured
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to toggle featured status', $e->getMessage());
        }
    }

    /**
     * Bulk update property status (Admin)
     * POST /api/v1/admin/properties/bulk-update-status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array',
            'property_ids.*' => 'exists:properties,id',
            'status' => 'required|in:active,sold,pending,draft'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $updated = Property::whereIn('id', $request->property_ids)
                             ->update(['status' => $request->status]);

            return successResponse("Updated {$updated} properties to {$request->status} status", [
                'updated_count' => $updated,
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to bulk update properties', $e->getMessage());
        }
    }

    // PRIVATE HELPER METHODS

    private function applyPublicFilters($query, Request $request)
    {
        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Listing type filter
        if ($request->has('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Location filters
        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->has('county')) {
            $query->where('county', 'like', '%' . $request->county . '%');
        }

        // Price range filter
        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        } elseif ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        } elseif ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Bedroom filter
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        // Bathroom filter
        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        // Square footage filter
        if ($request->has('min_sqft')) {
            $query->where('square_footage', '>=', $request->min_sqft);
        }

        // Featured filter
        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }
    }

    private function applyAdminFilters($query, Request $request)
    {
        // Status filter (admin can see all statuses)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Listing type filter
        if ($request->has('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Featured filter
        if ($request->has('featured')) {
            $query->where('is_featured', $request->featured);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }
    }

    private function transformProperty($property)
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'slug' => $property->slug,
            'description' => $property->description,
            'type' => $property->type,
            'listing_type' => $property->listing_type,
            'price' => $property->price,
            'formatted_price' => $property->formatted_price,
            'currency' => $property->currency,
            'location' => [
                'address' => $property->address,
                'city' => $property->city,
                'county' => $property->county,
                'country' => $property->country,
                'coordinates' => [
                    'lat' => $property->latitude,
                    'lng' => $property->longitude,
                ]
            ],
            'details' => [
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'square_footage' => $property->square_footage,
                'lot_size' => $property->lot_size,
                'year_built' => $property->year_built,
                'parking_spaces' => $property->parking_spaces,
            ],
            'amenities' => $property->amenities,
            'features' => $property->features,
            'media' => [
                'featured_image' => $property->featured_image_url,
                'images' => $property->image_gallery,
                'documents' => $property->documents,
                'floor_plans' => $property->floor_plans,
            ],
            'virtual_tour_url' => $property->virtual_tour_url,
            'is_featured' => $property->is_featured,
            'view_count' => $property->view_count,
            'created_at' => $property->created_at->format('Y-m-d H:i:s'),
        ];
    }

    private function transformPropertySummary($property)
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'slug' => $property->slug,
            'type' => $property->type,
            'status' => $property->status,
            'listing_type' => $property->listing_type,
            'price' => $property->price,
            'formatted_price' => $property->formatted_price,
            'address' => $property->address,
            'city' => $property->city,
            'county' => $property->county,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'is_featured' => $property->is_featured,
            'view_count' => $property->view_count,
            'featured_image' => $property->featured_image_url,
            'created_at' => $property->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $property->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    private function transformPropertyDetail($property)
    {
        return [
            'id' => $property->id,
            'title' => $property->title,
            'slug' => $property->slug,
            'description' => $property->description,
            'type' => $property->type,
            'status' => $property->status,
            'listing_type' => $property->listing_type,
            'price' => $property->price,
            'currency' => $property->currency,
            'location' => [
                'address' => $property->address,
                'city' => $property->city,
                'county' => $property->county,
                'country' => $property->country,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
            ],
            'details' => [
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'square_footage' => $property->square_footage,
                'lot_size' => $property->lot_size,
                'year_built' => $property->year_built,
                'parking_spaces' => $property->parking_spaces,
            ],
            'amenities' => $property->amenities,
            'features' => $property->features,
            'virtual_tour_url' => $property->virtual_tour_url,
            'is_featured' => $property->is_featured,
            'view_count' => $property->view_count,
            'media' => [
                'featured_image' => $property->featured_image_url,
                'images' => $property->image_gallery,
                'documents' => $property->documents,
                'floor_plans' => $property->floor_plans,
            ],
            'created_at' => $property->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $property->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}