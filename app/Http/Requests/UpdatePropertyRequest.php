<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read array $amenities
 * @method bool filled(string $key)
 * @method array except(array|string $keys)
 * @method bool hasFile(string $key)
 * @method \Illuminate\Http\UploadedFile|array|null file(string|null $key = null, mixed $default = null)
 * @method mixed get(string $key, mixed $default = null)
 * @method bool has(string|array $key)
 */
class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'min:20'],
            'property_type_id' => ['sometimes', 'exists:property_types,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'property_use_category_id' => ['nullable', 'exists:property_use_categories,id'],
            'property_style_id' => ['nullable', 'exists:property_styles,id'],

            'status' => ['sometimes', 'in:available,sold,pending,draft,rented'],
            'listing_type' => ['sometimes', 'in:sale,rent,lease'],

            'price' => ['sometimes', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['sometimes', 'string', 'max:3'],

            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'bedrooms' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'area' => ['sometimes', 'numeric', 'min:0'],
            'lot_size' => ['sometimes', 'numeric', 'min:0'],
            'year_built' => ['sometimes', 'integer', 'min:1800', 'max:' . (date('Y') + 2)],
            'garage_spaces' => ['sometimes', 'integer', 'min:0', 'max:20'],

            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['exists:amenities,id'],

            'neighborhood_ratings' => ['sometimes', 'array'],
            'neighborhood_ratings.schools' => ['sometimes', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.restaurants' => ['sometimes', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.transit' => ['sometimes', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.shopping' => ['sometimes', 'integer', 'min:0', 'max:10'],

            'is_featured' => ['sometimes', 'boolean'],
            'is_best_deal' => ['sometimes', 'boolean'],

            'virtual_tour_url' => ['nullable', 'url', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['sometimes', 'array'],

            'images' => ['sometimes', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
