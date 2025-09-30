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
class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'property_use_category_id' => ['nullable', 'exists:property_use_categories,id'],
            'property_style_id' => ['nullable', 'exists:property_styles,id'],

            'status' => ['nullable', 'in:available,sold,pending,draft,rented'],
            'listing_type' => ['required', 'in:sale,rent,lease'],

            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'currency' => ['nullable', 'string', 'max:3'],

            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'lot_size' => ['nullable', 'numeric', 'min:0'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 2)],
            'garage_spaces' => ['nullable', 'integer', 'min:0', 'max:20'],

            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],

            'neighborhood_ratings' => ['nullable', 'array'],
            'neighborhood_ratings.schools' => ['nullable', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.restaurants' => ['nullable', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.transit' => ['nullable', 'integer', 'min:0', 'max:10'],
            'neighborhood_ratings.shopping' => ['nullable', 'integer', 'min:0', 'max:10'],

            'is_featured' => ['nullable', 'boolean'],
            'is_best_deal' => ['nullable', 'boolean'],

            'virtual_tour_url' => ['nullable', 'url', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],

            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required',
            'description.min' => 'Description must be at least 20 characters',
            'property_type_id.required' => 'Please select a property type',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'listing_type.required' => 'Please specify if property is for sale, rent, or lease',
            'images.*.max' => 'Each image must not exceed 5MB',
        ];
    }
}
