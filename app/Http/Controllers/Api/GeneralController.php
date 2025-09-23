<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    /**
     * Get homepage data (Public)
     * GET /api/public/homepage
     */
    public function homepage()
    {
        try {
            // Get featured properties
            $featuredProperties = Property::with('media')
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
                                            ];
                                        });

            // Get recent blog posts
            $recentPosts = BlogPost::published()
                                 ->with('media')
                                 ->orderBy('published_at', 'desc')
                                 ->limit(3)
                                 ->get()
                                 ->map(function($post) {
                                     return [
                                         'id' => $post->id,
                                         'title' => $post->title,
                                         'slug' => $post->slug,
                                         'excerpt' => $post->excerpt,
                                         'featured_image' => $post->featured_image_url,
                                         'published_at' => $post->published_at->format('Y-m-d H:i:s'),
                                     ];
                                 });

            // Get basic statistics
            $stats = [
                'total_properties' => Property::active()->count(),
                'properties_sold' => Property::where('status', 'sold')->count(),
                'happy_clients' => Property::where('status', 'sold')->count() * 1.2, // Approximate
                'years_experience' => date('Y') - 2015, // Company founded in 2015
            ];

            return successResponse('Homepage data retrieved successfully', [
                'featured_properties' => $featuredProperties,
                'recent_posts' => $recentPosts,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve homepage data', $e->getMessage());
        }
    }

    /**
     * Get about page data (Public)
     * GET /api/public/about
     */
    public function aboutPage()
    {
        try {
            // Company stats
            $stats = [
                'total_properties' => Property::active()->count(),
                'properties_sold' => Property::where('status', 'sold')->count(),
                'years_experience' => date('Y') - 2015,
                'areas_covered' => Property::distinct('city')->count(),
            ];

            return successResponse('About page data retrieved successfully', [
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve about page data', $e->getMessage());
        }
    }

    /**
     * Get search suggestions (Public)
     * GET /api/public/search-suggestions
     */
    public function searchSuggestions(Request $request)
    {
        try {
            $query = $request->input('q', '');

            if (strlen($query) < 2) {
                return successResponse('Search suggestions retrieved successfully', []);
            }

            // Get city suggestions
            $cities = Property::active()
                           ->where('city', 'like', '%' . $query . '%')
                           ->distinct('city')
                           ->limit(5)
                           ->pluck('city')
                           ->map(function($city) {
                               return [
                                   'type' => 'city',
                                   'label' => $city,
                                   'value' => $city,
                               ];
                           });

            // Get property title suggestions
            $properties = Property::active()
                               ->where('title', 'like', '%' . $query . '%')
                               ->limit(5)
                               ->get(['id', 'title', 'slug'])
                               ->map(function($property) {
                                   return [
                                       'type' => 'property',
                                       'label' => $property->title,
                                       'value' => $property->slug,
                                       'id' => $property->id,
                                   ];
                               });

            $suggestions = $cities->concat($properties)->take(10);

            return successResponse('Search suggestions retrieved successfully', $suggestions);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve search suggestions', $e->getMessage());
        }
    }
}
