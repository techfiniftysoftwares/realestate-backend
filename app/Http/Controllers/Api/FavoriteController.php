<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    /**
     * Get all user's favorites
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $favorites = $user->favorites()
                ->with(['property.propertyType', 'property.location', 'property.amenities'])
                ->latest()
                ->get();

            $properties = $favorites->pluck('property');

            return successResponse(
                'Favorites retrieved successfully',
                [
                    'favorites' => PropertyResource::collection($properties),
                    'count' => $favorites->count()
                ]
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve favorites', $e->getMessage());
        }
    }

    /**
     * Add property to favorites
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();
            $propertyId = $request->property_id;

            // Check if already favorited
            $exists = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->exists();

            if ($exists) {
                return errorResponse('Property already in favorites', 409);
            }

            $favorite = Favorite::create([
                'user_id' => $user->id,
                'property_id' => $propertyId
            ]);

            $favorite->load(['property.propertyType', 'property.location', 'property.amenities']);

            return createdResponse(
                new PropertyResource($favorite->property),
                'Property added to favorites'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to add favorite', $e->getMessage());
        }
    }

    /**
     * Remove property from favorites
     */
    public function destroy($propertyId)
    {
        try {
            $user = Auth::user();

            $favorite = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->first();

            if (!$favorite) {
                return notFoundResponse('Property not in favorites');
            }

            $favorite->delete();

            return deleteResponse('Property removed from favorites');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to remove favorite', $e->getMessage());
        }
    }

    /**
     * Toggle favorite status
     */
    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();
            $propertyId = $request->property_id;

            $favorite = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->first();

            if ($favorite) {
                // Remove from favorites
                $favorite->delete();

                return successResponse(
                    'Property removed from favorites',
                    ['is_favorited' => false]
                );
            } else {
                // Add to favorites
                $favorite = Favorite::create([
                    'user_id' => $user->id,
                    'property_id' => $propertyId
                ]);

                $favorite->load(['property.propertyType', 'property.location', 'property.amenities']);

                return successResponse(
                    'Property added to favorites',
                    [
                        'is_favorited' => true,
                        'favorite' => new PropertyResource($favorite->property)
                    ]
                );
            }

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to toggle favorite', $e->getMessage());
        }
    }

    /**
     * Check if property is favorited
     */
    public function check($propertyId)
    {
        try {
            $user = Auth::user();

            $isFavorited = Favorite::where('user_id', $user->id)
                ->where('property_id', $propertyId)
                ->exists();

            return successResponse(
                'Favorite status retrieved',
                ['is_favorited' => $isFavorited]
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to check favorite status', $e->getMessage());
        }
    }

    /**
     * Get favorites count
     */
    public function count()
    {
        try {
            $user = Auth::user();
            $count = Favorite::where('user_id', $user->id)->count();

            return successResponse(
                'Favorites count retrieved',
                ['count' => $count]
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to get favorites count', $e->getMessage());
        }
    }

    /**
     * Clear all favorites
     */
    public function clear()
    {
        try {
            $user = Auth::user();
            $count = Favorite::where('user_id', $user->id)->delete();

            return successResponse(
                'All favorites cleared',
                ['deleted_count' => $count]
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to clear favorites', $e->getMessage());
        }
    }
}
