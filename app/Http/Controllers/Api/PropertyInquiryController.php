<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\PropertyInquiry;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\PropertyInquirySubmitted;
use Illuminate\Support\Facades\Log;

class PropertyInquiryController extends Controller
{
    // PUBLIC METHODS (No authentication required)

    /**
     * Submit a property inquiry (Public)
     * POST /api/v1/public/property-inquiry
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'required|email|max:255',
            'visitor_phone' => 'nullable|string|max:20',
            'inquiry_type' => 'required|in:general,viewing,offer,information',
            'message' => 'nullable|string|max:2000',
            'preferred_viewing_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            // Check if property is active
            $property = Property::where('id', $request->property_id)
                              ->where('status', 'active')
                              ->first();

            if (!$property) {
                return notFoundResponse('Property not found or not available for inquiries');
            }

            // Check for spam - limit inquiries from same IP
            $recentInquiries = PropertyInquiry::where('property_id', $request->property_id)
                                           ->where('ip_address', $request->ip())
                                           ->where('created_at', '>', now()->subHours(2))
                                           ->count();

            if ($recentInquiries >= 3) {
                return errorResponse('Too many recent inquiries. Please try again later.', 429);
            }

            $inquiry = PropertyInquiry::create([
                'property_id' => $request->property_id,
                'visitor_name' => $request->visitor_name,
                'visitor_email' => $request->visitor_email,
                'visitor_phone' => $request->visitor_phone,
                'inquiry_type' => $request->inquiry_type,
                'message' => $request->message,
                'preferred_viewing_date' => $request->preferred_viewing_date,
                'ip_address' => $request->ip(),
            ]);

            $inquiry->load('property:id,title,slug,address');

            // Send notification email to admin
            try {
                Mail::to(config('app.admin_email', 'info@opalluxerealty.com'))
                    ->send(new PropertyInquirySubmitted($inquiry));
            } catch (\Exception $e) {
               Log::error('Failed to send property inquiry notification: ' . $e->getMessage());
            }

            return createdResponse(
                [
                    'id' => $inquiry->id,
                    'property' => [
                        'id' => $inquiry->property->id,
                        'title' => $inquiry->property->title,
                        'slug' => $inquiry->property->slug,
                    ],
                    'inquiry_type' => $inquiry->inquiry_type,
                    'message' => 'Thank you for your inquiry about this property. We will contact you soon.'
                ],
                'Property inquiry submitted successfully'
            );

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to submit property inquiry', $e->getMessage());
        }
    }

    /**
     * Check if property accepts inquiries (Public)
     * GET /api/v1/public/properties/{id}/inquiry-availability
     */
    public function checkAvailability($propertyId, Request $request)
    {
        try {
            $validator = Validator::make(['property_id' => $propertyId], [
                'property_id' => 'required|exists:properties,id'
            ]);

            if ($validator->fails()) {
                return validationErrorResponse($validator->errors());
            }

            $property = Property::where('id', $propertyId)
                              ->where('status', 'active')
                              ->first(['id', 'title', 'status']);

            if (!$property) {
                return successResponse('Property not available for inquiries', [
                    'available' => false,
                    'reason' => 'Property not found or not active'
                ]);
            }

            // Check recent inquiries from same IP to prevent spam
            $recentInquiries = PropertyInquiry::where('property_id', $propertyId)
                                           ->where('ip_address', $request->ip())
                                           ->where('created_at', '>', now()->subHours(2))
                                           ->count();

            if ($recentInquiries >= 3) {
                return successResponse('Inquiry limit reached', [
                    'available' => false,
                    'reason' => 'Too many recent inquiries from this location'
                ]);
            }

            return successResponse('Property available for inquiries', [
                'available' => true,
                'property' => [
                    'id' => $property->id,
                    'title' => $property->title,
                ]
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to check availability', $e->getMessage());
        }
    }

    // ADMIN METHODS (Authentication required)

    /**
     * Get all property inquiries (Admin)
     * GET /api/v1/admin/property-inquiries
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_direction', 'desc');

            $allowedSortFields = ['created_at', 'visitor_name', 'inquiry_type', 'status', 'preferred_viewing_date'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            $query = PropertyInquiry::with(['property:id,title,slug,address']);

            // Apply filters
            $this->applyFilters($query, $request);

            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');

            $inquiries = $query->paginate($perPage);

            if ($inquiries->isEmpty()) {
                return successResponse('No property inquiries found', [
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ]
                ]);
            }

            $transformedInquiries = $inquiries->through(function ($inquiry) {
                return $this->transformInquiry($inquiry);
            });

            return paginatedResponse($transformedInquiries, 'Property inquiries retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve property inquiries', $e->getMessage());
        }
    }

    /**
     * Get single property inquiry (Admin)
     * GET /api/v1/admin/property-inquiries/{id}
     */
    public function show(PropertyInquiry $propertyInquiry)
    {
        try {
            $propertyInquiry->load('property');

            $data = [
                'id' => $propertyInquiry->id,
                'visitor_name' => $propertyInquiry->visitor_name,
                'visitor_email' => $propertyInquiry->visitor_email,
                'visitor_phone' => $propertyInquiry->visitor_phone,
                'inquiry_type' => $propertyInquiry->inquiry_type,
                'message' => $propertyInquiry->message,
                'preferred_viewing_date' => $propertyInquiry->preferred_viewing_date ?
                    $propertyInquiry->preferred_viewing_date->format('Y-m-d H:i:s') : null,
                'status' => $propertyInquiry->status,
                'ip_address' => $propertyInquiry->ip_address,
                'property' => $propertyInquiry->property ? [
                    'id' => $propertyInquiry->property->id,
                    'title' => $propertyInquiry->property->title,
                    'slug' => $propertyInquiry->property->slug,
                    'address' => $propertyInquiry->property->address,
                    'type' => $propertyInquiry->property->type,
                    'listing_type' => $propertyInquiry->property->listing_type,
                    'price' => $propertyInquiry->property->formatted_price,
                ] : null,
                'created_at' => $propertyInquiry->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $propertyInquiry->updated_at->format('Y-m-d H:i:s'),
            ];

            return successResponse('Property inquiry retrieved successfully', $data);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve property inquiry', $e->getMessage());
        }
    }

    /**
     * Update property inquiry (Admin)
     * PUT /api/v1/admin/property-inquiries/{id}
     */
    public function update(Request $request, PropertyInquiry $propertyInquiry)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,scheduled,completed,cancelled',
            'preferred_viewing_date' => 'sometimes|nullable|date|after:now',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $propertyInquiry->update($validator->validated());
            $propertyInquiry->load('property');

            return updatedResponse(
                $this->transformInquiry($propertyInquiry),
                'Property inquiry updated successfully'
            );

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to update property inquiry', $e->getMessage());
        }
    }

    /**
     * Delete property inquiry (Admin)
     * DELETE /api/v1/admin/property-inquiries/{id}
     */
    public function destroy(PropertyInquiry $propertyInquiry)
    {
        try {
            $propertyInquiry->delete();
            return deleteResponse('Property inquiry deleted successfully');
        } catch (\Exception $e) {
            return queryErrorResponse('Failed to delete property inquiry', $e->getMessage());
        }
    }

    /**
     * Bulk update inquiry status (Admin)
     * POST /api/v1/admin/property-inquiries/bulk-update
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inquiry_ids' => 'required|array',
            'inquiry_ids.*' => 'exists:property_inquiries,id',
            'status' => 'required|in:pending,scheduled,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $updated = PropertyInquiry::whereIn('id', $request->inquiry_ids)
                                   ->update(['status' => $request->status]);

            return successResponse("Updated {$updated} inquiries to {$request->status} status", [
                'updated_count' => $updated,
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to bulk update inquiries', $e->getMessage());
        }
    }

    /**
     * Schedule viewing for inquiry (Admin)
     * POST /api/v1/admin/property-inquiries/{id}/schedule
     */
    public function scheduleViewing(PropertyInquiry $propertyInquiry, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferred_viewing_date' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $propertyInquiry->update([
                'status' => 'scheduled',
                'preferred_viewing_date' => $request->preferred_viewing_date,
            ]);

            return successResponse('Viewing scheduled successfully', [
                'id' => $propertyInquiry->id,
                'status' => $propertyInquiry->status,
                'preferred_viewing_date' => $propertyInquiry->preferred_viewing_date->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to schedule viewing', $e->getMessage());
        }
    }

    /**
     * Get inquiry statistics (Admin)
     * GET /api/v1/admin/property-inquiries/stats
     */
    public function getStats(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->startOfMonth());
            $dateTo = $request->input('date_to', now()->endOfMonth());

            $stats = [
                'total_inquiries' => PropertyInquiry::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'pending' => PropertyInquiry::where('status', 'pending')
                                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                                          ->count(),
                'scheduled' => PropertyInquiry::where('status', 'scheduled')
                                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                                            ->count(),
                'completed' => PropertyInquiry::where('status', 'completed')
                                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                                            ->count(),
                'cancelled' => PropertyInquiry::where('status', 'cancelled')
                                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                                            ->count(),
                'by_type' => PropertyInquiry::selectRaw('inquiry_type, COUNT(*) as count')
                                          ->whereBetween('created_at', [$dateFrom, $dateTo])
                                          ->groupBy('inquiry_type')
                                          ->get()
                                          ->pluck('count', 'inquiry_type'),
                'top_properties' => PropertyInquiry::selectRaw('property_id, COUNT(*) as inquiry_count')
                                                 ->with('property:id,title,slug')
                                                 ->whereBetween('created_at', [$dateFrom, $dateTo])
                                                 ->groupBy('property_id')
                                                 ->orderByDesc('inquiry_count')
                                                 ->limit(5)
                                                 ->get()
                                                 ->map(function($item) {
                                                     return [
                                                         'property' => $item->property ? [
                                                             'id' => $item->property->id,
                                                             'title' => $item->property->title,
                                                             'slug' => $item->property->slug,
                                                         ] : null,
                                                         'inquiry_count' => $item->inquiry_count
                                                     ];
                                                 })
            ];

            return successResponse('Inquiry statistics retrieved successfully', $stats);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve inquiry statistics', $e->getMessage());
        }
    }

    /**
     * Get upcoming viewings (Admin)
     * GET /api/v1/admin/property-inquiries/upcoming-viewings
     */
    public function getUpcomingViewings(Request $request)
    {
        try {
            $days = $request->input('days', 7);
            $startDate = now();
            $endDate = now()->addDays($days);

            $viewings = PropertyInquiry::with('property:id,title,slug,address')
                                     ->where('status', 'scheduled')
                                     ->whereBetween('preferred_viewing_date', [$startDate, $endDate])
                                     ->orderBy('preferred_viewing_date')
                                     ->get()
                                     ->map(function($inquiry) {
                                         return [
                                             'id' => $inquiry->id,
                                             'visitor_name' => $inquiry->visitor_name,
                                             'visitor_email' => $inquiry->visitor_email,
                                             'visitor_phone' => $inquiry->visitor_phone,
                                             'viewing_date' => $inquiry->preferred_viewing_date->format('Y-m-d H:i:s'),
                                             'property' => $inquiry->property ? [
                                                 'id' => $inquiry->property->id,
                                                 'title' => $inquiry->property->title,
                                                 'slug' => $inquiry->property->slug,
                                                 'address' => $inquiry->property->address,
                                             ] : null,
                                         ];
                                     });

            return successResponse('Upcoming viewings retrieved successfully', $viewings);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve upcoming viewings', $e->getMessage());
        }
    }

    // PRIVATE HELPER METHODS

    private function applyFilters($query, Request $request)
    {
        // Property filter
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Inquiry type filter
        if ($request->has('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('visitor_name', 'like', '%' . $search . '%')
                  ->orWhere('visitor_email', 'like', '%' . $search . '%')
                  ->orWhere('visitor_phone', 'like', '%' . $search . '%')
                  ->orWhereHas('property', function($propertyQuery) use ($search) {
                      $propertyQuery->where('title', 'like', '%' . $search . '%')
                                  ->orWhere('address', 'like', '%' . $search . '%');
                  });
            });
        }

        // Viewing date filter
        if ($request->has('viewing_date_from')) {
            $query->whereDate('preferred_viewing_date', '>=', $request->viewing_date_from);
        }

        if ($request->has('viewing_date_to')) {
            $query->whereDate('preferred_viewing_date', '<=', $request->viewing_date_to);
        }
    }

    private function transformInquiry($inquiry)
    {
        return [
            'id' => $inquiry->id,
            'visitor_name' => $inquiry->visitor_name,
            'visitor_email' => $inquiry->visitor_email,
            'visitor_phone' => $inquiry->visitor_phone,
            'inquiry_type' => $inquiry->inquiry_type,
            'message' => $inquiry->message,
            'preferred_viewing_date' => $inquiry->preferred_viewing_date ?
                $inquiry->preferred_viewing_date->format('Y-m-d H:i:s') : null,
            'status' => $inquiry->status,
            'property' => $inquiry->property ? [
                'id' => $inquiry->property->id,
                'title' => $inquiry->property->title,
                'slug' => $inquiry->property->slug,
                'address' => $inquiry->property->address,
            ] : null,
            'created_at' => $inquiry->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $inquiry->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
