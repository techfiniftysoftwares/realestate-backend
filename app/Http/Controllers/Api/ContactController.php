<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\PropertyInquiry;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormSubmitted;
use App\Mail\PropertyInquirySubmitted;


class ContactController extends Controller
{
    public function submitContactForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $submission = ContactSubmission::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'ip_address' => $request->ip(),
            ]);

            // Send notification email to admin
            try {
                Mail::to(config('app.admin_email', 'info@opalluxerealty.com'))
                    ->send(new ContactFormSubmitted($submission));
            } catch (\Exception $e) {
               Log::error('Failed to send contact form notification: ' . $e->getMessage());
            }

            return createdResponse(
                [
                    'id' => $submission->id,
                    'message' => 'Thank you for your message. We will get back to you soon.'
                ],
                'Contact form submitted successfully'
            );

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to submit contact form', $e->getMessage());
        }
    }

    public function submitPropertyInquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'required|email|max:255',
            'visitor_phone' => 'nullable|string|max:20',
            'inquiry_type' => 'required|in:general,viewing,offer,information',
            'message' => 'nullable|string|max:1000',
            'preferred_viewing_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
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

            $inquiry->load('property');

            // Send notification email
            try {
                Mail::to(config('app.admin_email', 'info@opalluxerealty.com'))
                    ->send(new PropertyInquirySubmitted($inquiry));
            } catch (\Exception $e) {
               Log::error('Failed to send property inquiry notification: ' . $e->getMessage());
            }

            return createdResponse(
                [
                    'id' => $inquiry->id,
                    'message' => 'Thank you for your inquiry. We will contact you soon.'
                ],
                'Property inquiry submitted successfully'
            );

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to submit property inquiry', $e->getMessage());
        }
    }

    public function subscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'preferences' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $subscriber = NewsletterSubscriber::create([
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'preferences' => $request->preferences,
                'ip_address' => $request->ip(),
            ]);

            return createdResponse(
                [
                    'id' => $subscriber->id,
                    'message' => 'Successfully subscribed to our newsletter!'
                ],
                'Newsletter subscription successful'
            );

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to subscribe to newsletter', $e->getMessage());
        }
    }

    public function unsubscribeNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:newsletter_subscribers,email'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            NewsletterSubscriber::where('email', $request->email)
                              ->update(['status' => 'unsubscribed']);

            return successResponse('Successfully unsubscribed from newsletter');

        } catch (\Exception $e) {
            return queryErrorResponse('Failed to unsubscribe from newsletter', $e->getMessage());
        }
    }
}
