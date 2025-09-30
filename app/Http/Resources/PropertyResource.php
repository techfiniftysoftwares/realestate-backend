<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Property
 */
class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,

            // Price
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,

            // Location
            'location' => $this->location?->name,
            'location_details' => [
                'id' => $this->location?->id,
                'name' => $this->location?->name,
                'city' => $this->location?->city,
                'county' => $this->location?->county,
                'country' => $this->location?->country,
            ],

            // Type & Category
            'type' => $this->propertyType->slug,
            'type_name' => $this->propertyType->name,
            'type_icon' => $this->propertyType->icon,
            'use_category' => $this->propertyUseCategory?->name,
            'style' => $this->propertyStyle?->name,

            // Property Details
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area' => $this->area,
            'lot' => $this->lot_size,
            'garage' => $this->garage_spaces,
            'year_built' => $this->year_built,

            // Features
            'amenities' => $this->amenities->map(fn($amenity) => [
                'id' => $amenity->id,
                'name' => $amenity->name,
                'icon' => $amenity->icon,
            ]),

            // Images
            'images' => $this->getImageUrls(),
            'featured_image' => $this->getFeaturedImageUrl(),

            // Status & Flags
            'status' => $this->status,
            'listing_type' => $this->listing_type,
            'featured' => $this->is_featured,
            'bestDeal' => $this->is_best_deal,

            // Additional Info
            'neighborhood' => $this->neighborhood_ratings,
            'virtual_tour_url' => $this->virtual_tour_url,
            'view_count' => $this->view_count,

            // Agent
            'agent' => $this->when($this->agent, [
                'id' => $this->agent?->id,
                'name' => $this->agent?->name,
                'email' => $this->agent?->email,
            ]),

            // Favorite Status (only if user is authenticated)
            'is_favorited' => $this->when(
                Auth::check(),
                fn() => $this->isFavoritedBy(Auth::id())
            ),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
