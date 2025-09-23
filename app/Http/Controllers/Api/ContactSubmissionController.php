<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactSubmissionController extends Controller
{
    /**
     * Get all contact submissions (Admin)
     * GET /api/contact-submissions
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);

            $query = ContactSubmission::query();

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
                    'status' => $submission->status,
                    'created_at' => $submission->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return paginatedResponse($transformedSubmissions, 'Contact submissions retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve contact submissions', $e->getMessage());
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

            $data = [
                'id' => $contactSubmission->id,
                'name' => $contactSubmission->name,
                'email' => $contactSubmission->email,
                'phone' => $contactSubmission->phone,
                'subject' => $contactSubmission->subject,
                'message' => $contactSubmission->message,
                'status' => $contactSubmission->status,
                'ip_address' => $contactSubmission->ip_address,
                'created_at' => $contactSubmission->created_at->format('Y-m-d H:i:s'),
            ];

            return successResponse('Contact submission retrieved successfully', $data);

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve contact submission', $e->getMessage());
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
            return queryErrorResponse('Failed to update contact submission', $e->getMessage());
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
            return queryErrorResponse('Failed to delete contact submission', $e->getMessage());
        }
    }
}
