<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyView;
use App\Models\BlogPost;
use App\Models\ContactSubmission;
use App\Models\PropertyInquiry;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard statistics (Admin)
     * GET /api/dashboard
     */
    public function dashboard()
    {
        try {
            $data = [
                'properties' => [
                    'total' => Property::count(),
                    'active' => Property::where('status', 'active')->count(),
                    'sold' => Property::where('status', 'sold')->count(),
                    'pending' => Property::where('status', 'pending')->count(),
                    'featured' => Property::where('is_featured', true)->count(),
                    'added_this_month' => Property::whereMonth('created_at', now()->month)->count(),
                ],
                'views' => [
                    'total_property_views' => PropertyView::count(),
                    'today' => PropertyView::whereDate('viewed_at', today())->count(),
                    'this_week' => PropertyView::whereBetween('viewed_at', [
                        now()->startOfWeek(), now()->endOfWeek()
                    ])->count(),
                    'this_month' => PropertyView::whereMonth('viewed_at', now()->month)->count(),
                ],
                'inquiries' => [
                    'total' => PropertyInquiry::count(),
                    'pending' => PropertyInquiry::where('status', 'pending')->count(),
                    'this_month' => PropertyInquiry::whereMonth('created_at', now()->month)->count(),
                    'by_type' => PropertyInquiry::selectRaw('inquiry_type, COUNT(*) as count')
                                              ->groupBy('inquiry_type')
                                              ->get()
                                              ->pluck('count', 'inquiry_type'),
                ],
                'contact_submissions' => [
                    'total' => ContactSubmission::count(),
                    'new' => ContactSubmission::where('status', 'new')->count(),
                    'this_month' => ContactSubmission::whereMonth('created_at', now()->month)->count(),
                ],
                'newsletter' => [
                    'total_subscribers' => NewsletterSubscriber::where('status', 'active')->count(),
                    'new_this_month' => NewsletterSubscriber::where('status', 'active')
                                                           ->whereMonth('created_at', now()->month)
                                                           ->count(),
                ],
                'blog' => [
                    'total_posts' => BlogPost::count(),
                    'published' => BlogPost::where('status', 'published')->count(),
                    'total_views' => BlogPost::sum('view_count'),
                    'published_this_month' => BlogPost::where('status', 'published')
                                                     ->whereMonth('created_at', now()->month)
                                                     ->count(),
                ]
            ];

            return successResponse('Dashboard statistics retrieved successfully', $data);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve dashboard statistics', $e->getMessage());
        }
    }

    /**
     * Get property views chart data (Admin)
     * GET /api/analytics/property-views
     */
    public function propertyViews(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $startDate = now()->subDays($days);

            $views = PropertyView::selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
                               ->where('viewed_at', '>=', $startDate)
                               ->groupBy('date')
                               ->orderBy('date')
                               ->get();

            return successResponse('Property views chart data retrieved successfully', $views);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve property views data', $e->getMessage());
        }
    }

    /**
     * Get top properties by views (Admin)
     * GET /api/analytics/top-properties
     */
    public function topProperties(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $properties = Property::withCount('views')
                                ->orderBy('views_count', 'desc')
                                ->limit($limit)
                                ->get(['id', 'title', 'slug', 'price'])
                                ->map(function($property) {
                                    return [
                                        'id' => $property->id,
                                        'title' => $property->title,
                                        'slug' => $property->slug,
                                        'price' => $property->formatted_price,
                                        'views' => $property->views_count,
                                    ];
                                });

            return successResponse('Top properties retrieved successfully', $properties);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve top properties', $e->getMessage());
        }
    }
}
