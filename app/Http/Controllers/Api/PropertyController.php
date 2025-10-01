<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties with advanced filters
     */
    #[OA\Get(
        path: "/api/properties",
        summary: "Get all properties with filters",
        description: "Retrieve a paginated list of properties with advanced filtering, sorting, and search capabilities",
        tags: ["Properties"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search term for property title or description",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "type_id",
                in: "query",
                description: "Filter by property type ID",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "location_id",
                in: "query",
                description: "Filter by location ID",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "min_price",
                in: "query",
                description: "Minimum price filter",
                required: false,
                schema: new OA\Schema(type: "number", format: "float")
            ),
            new OA\Parameter(
                name: "max_price",
                in: "query",
                description: "Maximum price filter",
                required: false,
                schema: new OA\Schema(type: "number", format: "float")
            ),
            new OA\Parameter(
                name: "bedrooms",
                in: "query",
                description: "Minimum number of bedrooms",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "bathrooms",
                in: "query",
                description: "Minimum number of bathrooms",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "amenities",
                in: "query",
                description: "Array of amenity IDs",
                required: false,
                schema: new OA\Schema(type: "array", items: new OA\Items(type: "integer"))
            ),
            new OA\Parameter(
                name: "listing_type",
                in: "query",
                description: "Filter by listing type (sale/rent)",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["sale", "rent"])
            ),
            new OA\Parameter(
                name: "featured",
                in: "query",
                description: "Filter featured properties only",
                required: false,
                schema: new OA\Schema(type: "boolean")
            ),
            new OA\Parameter(
                name: "best_deal",
                in: "query",
                description: "Filter best deal properties only",
                required: false,
                schema: new OA\Schema(type: "boolean")
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Sort field",
                required: false,
                schema: new OA\Schema(
                    type: "string",
                    enum: ["created_at", "price", "bedrooms", "bathrooms", "area", "view_count", "title"],
                    default: "created_at"
                )
            ),
            new OA\Parameter(
                name: "sort_order",
                in: "query",
                description: "Sort order",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["asc", "desc"], default: "desc")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Items per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Properties retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Properties retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer"),
                                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                                new OA\Property(property: "total", type: "integer"),
                                new OA\Property(property: "per_page", type: "integer"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Get(
        path: "/api/properties/featured",
        summary: "Get featured properties",
        description: "Retrieve a list of featured properties (up to 6)",
        tags: ["Properties"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Featured properties retrieved successfully"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Get(
        path: "/api/properties/best-deals",
        summary: "Get best deal properties",
        description: "Retrieve a list of best deal properties (up to 6)",
        tags: ["Properties"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Best deals retrieved successfully"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Get(
        path: "/api/properties/{slug}",
        summary: "Get a single property by slug",
        description: "Retrieve detailed information about a specific property. View count is automatically incremented.",
        tags: ["Properties"],
        parameters: [
            new OA\Parameter(
                name: "slug",
                in: "path",
                description: "Property slug",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Property retrieved successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Property not found"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Post(
        path: "/api/properties",
        summary: "Create a new property",
        description: "Create a new property listing (requires authentication)",
        security: [["bearerAuth" => []]],
        tags: ["Properties"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["title", "price", "listing_type"],
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Luxury Villa in Westlands"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "price", type: "number", format: "float", example: 50000000),
                        new OA\Property(property: "listing_type", type: "string", enum: ["sale", "rent"]),
                        new OA\Property(property: "bedrooms", type: "integer", example: 4),
                        new OA\Property(property: "bathrooms", type: "integer", example: 3),
                        new OA\Property(property: "area", type: "number", format: "float", example: 250.5),
                        new OA\Property(property: "property_type_id", type: "integer"),
                        new OA\Property(property: "location_id", type: "integer"),
                        new OA\Property(
                            property: "amenities",
                            type: "array",
                            items: new OA\Items(type: "integer"),
                            example: [1, 2, 3]
                        ),
                        new OA\Property(
                            property: "images",
                            type: "array",
                            items: new OA\Items(type: "string", format: "binary")
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Property created successfully"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Put(
        path: "/api/properties/{id}",
        summary: "Update a property",
        description: "Update an existing property (requires authentication)",
        security: [["bearerAuth" => []]],
        tags: ["Properties"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Property ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "price", type: "number", format: "float"),
                        new OA\Property(property: "listing_type", type: "string", enum: ["sale", "rent"]),
                        new OA\Property(property: "bedrooms", type: "integer"),
                        new OA\Property(property: "bathrooms", type: "integer"),
                        new OA\Property(
                            property: "amenities",
                            type: "array",
                            items: new OA\Items(type: "integer")
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Property updated successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Property not found"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
    #[OA\Delete(
        path: "/api/properties/{id}",
        summary: "Delete a property",
        description: "Delete a property (requires authentication)",
        security: [["bearerAuth" => []]],
        tags: ["Properties"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Property ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Property deleted successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Property not found"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            ),
            new OA\Response(
                response: 500,
                description: "Server error"
            ),
        ]
    )]
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
