<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyTypeController extends Controller
{
    /**
     * Get all property types
     * GET /api/property-types
     */
    public function index(Request $request)
    {
        try {
            $query = PropertyType::query();

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Apply ordering
            $query->ordered();

            // With properties count if requested
            if ($request->boolean('with_count')) {
                $query->withCount('properties');
            }

            $propertyTypes = $query->get();

            return successResponse(
                'Property types retrieved successfully',
                $propertyTypes
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve property types', $e->getMessage());
        }
    }

    /**
     * Get single property type
     * GET /api/property-types/{id}
     */
    public function show($id)
    {
        try {
            $propertyType = PropertyType::withCount('properties')->find($id);

            if (!$propertyType) {
                return notFoundResponse('Property type not found');
            }

            return successResponse(
                'Property type retrieved successfully',
                $propertyType
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve property type', $e->getMessage());
        }
    }

    /**
     * Create new property type
     * POST /api/property-types
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:property_types,name',
            'slug' => 'nullable|string|max:255|unique:property_types,slug',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $propertyType = PropertyType::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'icon' => $request->icon,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'order' => $request->order ?? 0,
            ]);

            return createdResponse(
                $propertyType,
                'Property type created successfully'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to create property type', $e->getMessage());
        }
    }

    /**
     * Update property type
     * PUT /api/property-types/{id}
     */
    public function update(Request $request, $id)
    {
        $propertyType = PropertyType::find($id);

        if (!$propertyType) {
            return notFoundResponse('Property type not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:property_types,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:property_types,slug,' . $id,
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $propertyType->update($request->only([
                'name',
                'slug',
                'icon',
                'description',
                'is_active',
                'order'
            ]));

            return updatedResponse(
                $propertyType->fresh(),
                'Property type updated successfully'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to update property type', $e->getMessage());
        }
    }

    /**
     * Delete property type
     * DELETE /api/property-types/{id}
     */
    public function destroy($id)
    {
        try {
            $propertyType = PropertyType::find($id);

            if (!$propertyType) {
                return notFoundResponse('Property type not found');
            }

            // Check if property type has associated properties
            $propertiesCount = $propertyType->properties()->count();

            if ($propertiesCount > 0) {
                return errorResponse(
                    "Cannot delete property type. It has {$propertiesCount} associated properties.",
                    400
                );
            }

            $propertyType->delete();

            return deleteResponse('Property type deleted successfully');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to delete property type', $e->getMessage());
        }
    }

    /**
     * Toggle active status
     * PATCH /api/property-types/{id}/toggle
     */
    public function toggleActive($id)
    {
        try {
            $propertyType = PropertyType::find($id);

            if (!$propertyType) {
                return notFoundResponse('Property type not found');
            }

            $propertyType->update([
                'is_active' => !$propertyType->is_active
            ]);

            return updatedResponse(
                $propertyType,
                'Property type status updated successfully'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to toggle property type status', $e->getMessage());
        }
    }

    /**
     * Reorder property types
     * POST /api/property-types/reorder
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:property_types,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            foreach ($request->items as $item) {
                PropertyType::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }

            return successResponse('Property types reordered successfully');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to reorder property types', $e->getMessage());
        }
    }
}
