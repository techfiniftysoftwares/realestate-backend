<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);

            $query = NewsletterSubscriber::query();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('email', 'like', '%' . $search . '%')
                      ->orWhere('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            }

            $subscribers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $transformedSubscribers = $subscribers->through(function ($subscriber) {
                return [
                    'id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'first_name' => $subscriber->first_name,
                    'last_name' => $subscriber->last_name,
                    'full_name' => $subscriber->full_name,
                    'status' => $subscriber->status,
                    'preferences' => $subscriber->preferences,
                    'created_at' => $subscriber->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return paginatedResponse($transformedSubscribers, 'Newsletter subscribers retrieved successfully');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to retrieve newsletter subscribers', $e->getMessage());
        }
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        try {
            $subscriber->delete();
            return deleteResponse('Newsletter subscriber deleted successfully');
        } catch (\Exception $e) {
            return queryErrorResponse('Failed to delete newsletter subscriber', $e->getMessage());
        }
    }
}
