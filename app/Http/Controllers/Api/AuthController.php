<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordEmail;
use App\Jobs\SendSupportEmail;
use App\Jobs\SendAuthSMSNotificationJob;
use App\Jobs\SendUserAccountCreatedNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        // Determine if identifier is email or name/username
        $field = filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($field, $request->identifier)->first();

        if (!$user) {
            return notFoundResponse('User not found');
        }

        if (!$user->is_active) {
            $user->tokens()->delete();
            return errorResponse('User account is inactive', 403);
        }

        // Attempt authentication
        if (Auth::attempt([
            $field => $request->identifier,
            'password' => $request->password
        ])) {
            // Create access token directly (skipping OTP for now)
            $accessToken = $user->createToken('Api-Access')->accessToken;

            // Update last successful login
            $user->update(['last_login_at' => now()]);

            return successResponse('Login successful', [
                'token' => $accessToken,
                'user' => $user->load('role')
            ]);
        }

        return errorResponse('Invalid credentials', 401);
    }

    // public function verifyOTP(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|exists:users,id',
    //         'otp_code' => 'required|string|size:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return validationErrorResponse($validator->errors());
    //     }

    //     try {
    //         $userId = $request->input('user_id');
    //         $inputOTP = $request->input('otp_code');

    //         // Get stored OTP from cache
    //         $storedOTP = Cache::get("otp_{$userId}");

    //         if (!$storedOTP) {
    //             return errorResponse('OTP has expired. Please login again.', 400);
    //         }

    //         if ($storedOTP !== $inputOTP) {
    //             return errorResponse('Invalid OTP code.', 400);
    //         }

    //         // OTP is valid, create access token
    //         $user = User::find($userId);
    //         $accessToken = $user->createToken('Api-Access')->accessToken;

    //         // Update last successful login
    //         $user->update(['last_login_at' => now()]);

    //         // Clear OTP from cache
    //         Cache::forget("otp_{$userId}");

    //         return successResponse('Login successful', [
    //             'token' => $accessToken,
    //             'user' => $user
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('OTP verification failed', [
    //             'user_id' => $request->input('user_id'),
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return serverErrorResponse('OTP verification failed', $e->getMessage());
    //     }
    // }

    // public function resendOTP(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|exists:users,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return validationErrorResponse($validator->errors());
    //     }

    //     try {
    //         $userId = $request->input('user_id');
    //         $user = User::find($userId);

    //         // Check if user is still active
    //         if (!$user->is_active) {
    //             return errorResponse('User account is inactive', 403);
    //         }

    //         // Generate new OTP
    //         $otpCode = $this->generateOTP();

    //         // Store new OTP in cache with 5-minute expiration
    //         Cache::put("otp_{$user->id}", $otpCode, now()->addMinutes(5));

    //         // Send OTP email using notification system (isResend = true)
    //         \App\Jobs\SendOTPNotification::dispatch($user, $otpCode, true);

    //         // Send OTP SMS notification if user has phone number
    //         if (!empty($user->phone)) {
    //             $smsData = [
    //                 'user_id' => $user->id,
    //                 'user_name' => $user->name,
    //                 'phone_number' => $user->phone,
    //                 'otp_code' => $otpCode
    //             ];

    //             SendAuthSMSNotificationJob::dispatch('otp_resend', $smsData);
    //         }

    //         return successResponse('New OTP sent to your email and SMS.');
    //     } catch (\Exception $e) {
    //         Log::error('Failed to resend OTP', [
    //             'user_id' => $request->input('user_id'),
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return serverErrorResponse('Failed to resend OTP', $e->getMessage());
    //     }
    // }

    public function signup(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'role_id' => 'required|exists:roles,id',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return validationErrorResponse($validator->errors());
            }

            // Generate a random password
            $password = $this->generatePassword(10);

            $authUser = Auth::user();

            // Create a new user record
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($password);
            $user->phone = $request->input('phone');
            $user->role_id = $request->input('role_id');
            $user->is_active = $request->input('is_active', true);
            $user->save();

            // Load the user with necessary relationships for email
            $user = $user->fresh(['role']);

            // Send account created notification using NEW fast email system
            \App\Jobs\SendUserAccountCreatedNotification::dispatch($user, $password, $authUser);

            return successResponse(
                'User registered successfully. Account details and password will be sent to their email shortly.',
                ['user' => $user],
                201
            );
        } catch (\Exception $e) {
            Log::error('Failed to register user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->except(['password']),
                'auth_user' => isset($authUser) ? $authUser->id : null
            ]);

            return serverErrorResponse('Failed to register user', $e->getMessage());
        }
    }

    public function profileChange(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $updateData = collect($request->only([
                'name',
                'email',
                'phone'
            ]))->filter()->toArray();

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return updatedResponse($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return serverErrorResponse('Failed to update profile', $e->getMessage());
        }
    }



    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return successResponse('Successfully logged out');
        } catch (\Exception $e) {
            Log::error('Failed to logout', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            return serverErrorResponse('Failed to logout', $e->getMessage());
        }
    }

    /**
     * Reset password request
     */
    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user->is_active) {
                return errorResponse('User account is inactive', 403);
            }

            // Generate reset token/OTP
            $resetCode = $this->generateOTP();

            // Store reset code in cache with 15-minute expiration
            Cache::put("password_reset_{$user->id}", $resetCode, now()->addMinutes(15));

            // Send password reset email
            \App\Jobs\SendPasswordResetNotification::dispatch($user, $resetCode);

            // Send SMS notification if user has phone number
            if (!empty($user->phone)) {
                $smsData = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'phone_number' => $user->phone,
                    'reset_code' => $resetCode
                ];

                SendAuthSMSNotificationJob::dispatch('password_reset', $smsData);
            }

            return successResponse('Password reset code sent to your email and SMS.');
        } catch (\Exception $e) {
            Log::error('Failed to send password reset', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return serverErrorResponse('Failed to send password reset', $e->getMessage());
        }
    }

    /**
     * Reset password with code
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'reset_code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return validationErrorResponse($validator->errors());
        }

        try {
            $user = User::where('email', $request->email)->first();

            // Get stored reset code from cache
            $storedCode = Cache::get("password_reset_{$user->id}");

            if (!$storedCode) {
                return errorResponse('Reset code has expired. Please request a new one.', 400);
            }

            if ($storedCode !== $request->reset_code) {
                return errorResponse('Invalid reset code.', 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Clear reset code from cache
            Cache::forget("password_reset_{$user->id}");

            // Revoke all existing tokens for security
            $user->tokens()->delete();

            return successResponse('Password reset successfully. Please login with your new password.');
        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return serverErrorResponse('Failed to reset password', $e->getMessage());
        }
    }

    /**
     * Generate a secure random password
     *
     * @param int $length The length of the password
     * @return string The generated password
     */
    private function generatePassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_-=+;:,.?';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $all = $uppercase . $lowercase . $numbers . $special;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Generate a 6-digit OTP code
     *
     * @return string The generated OTP
     */
    private function generateOTP()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
