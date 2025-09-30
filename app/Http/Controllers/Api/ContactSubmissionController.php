<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactSubmissionController extends Controller
{
    /**
     * Submit contact form (Public)
     * POST /api/contact
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'preferred_contact' => 'nullable|in:email,phone',
            'property_type_id' => 'nullable|exists:property_types,id',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $contactSubmission = ContactSubmission::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'preferred_contact' => $request->preferred_contact ?? 'email',
                'property_type_id' => $request->property_type_id,
                'status' => 'new',
                'ip_address' => $request->ip(),
            ]);

            return createdResponse(
                [
                    'id' => $contactSubmission->id,
                    'message' => 'We have received your message and will get back to you soon.'
                ],
                'Contact form submitted successfully'
            );

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to submit contact form', $e->getMessage());
        }
    }

    /**
     * Get all contact submissions (Admin)
     * GET /api/contact-submissions
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);

            $query = ContactSubmission::with('propertyType');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('subject', 'like', '%' . $search . '%');
                });
            }

            $submissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedSubmissions = $submissions->through(function ($submission) {
                return [
                    'id' => $submission->id,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'phone' => $submission->phone,
                    'subject' => $submission->subject,
                    'message' => $submission->message,
                    'preferred_contact' => $submission->preferred_contact,
                    'property_type' => $submission->propertyType ? [
                        'id' => $submission->propertyType->id,
                        'name' => $submission->propertyType->name,
                        'slug' => $submission->propertyType->slug,
                    ] : null,
                    'status' => $submission->status,
                    'created_at' => $submission->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return paginatedResponse($transformedSubmissions, 'Contact submissions retrieved successfully');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve contact submissions', $e->getMessage());
        }
    }

    /**
     * Get single contact submission (Admin)
     * GET /api/contact-submissions/{id}
     */
    public function show(ContactSubmission $contactSubmission)
    {
        try {
            // Mark as read when viewed
            if ($contactSubmission->status === 'new') {
                $contactSubmission->update(['status' => 'read']);
            }

            $contactSubmission->load('propertyType');

            $data = [
                'id' => $contactSubmission->id,
                'name' => $contactSubmission->name,
                'email' => $contactSubmission->email,
                'phone' => $contactSubmission->phone,
                'subject' => $contactSubmission->subject,
                'message' => $contactSubmission->message,
                'preferred_contact' => $contactSubmission->preferred_contact,
                'property_type' => $contactSubmission->propertyType ? [
                    'id' => $contactSubmission->propertyType->id,
                    'name' => $contactSubmission->propertyType->name,
                    'slug' => $contactSubmission->propertyType->slug,
                ] : null,
                'status' => $contactSubmission->status,
                'ip_address' => $contactSubmission->ip_address,
                'created_at' => $contactSubmission->created_at->format('Y-m-d H:i:s'),
            ];

            return successResponse('Contact submission retrieved successfully', $data);

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to retrieve contact submission', $e->getMessage());
        }
    }

    /**
     * Update contact submission status (Admin)
     * PUT /api/contact-submissions/{id}
     */
    public function update(Request $request, ContactSubmission $contactSubmission)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,replied,archived'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $contactSubmission->update(['status' => $request->status]);

            return updatedResponse([
                'id' => $contactSubmission->id,
                'status' => $contactSubmission->status,
            ], 'Contact submission status updated successfully');

        } catch (\Exception $e) {
            return serverErrorResponse('Failed to update contact submission', $e->getMessage());
        }
    }

    /**
     * Delete contact submission (Admin)
     * DELETE /api/contact-submissions/{id}
     */
    public function destroy(ContactSubmission $contactSubmission)
    {
        try {
            $contactSubmission->delete();
            return deleteResponse('Contact submission deleted successfully');
        } catch (\Exception $e) {
            return serverErrorResponse('Failed to delete contact submission', $e->getMessage());
        }
    }
}
