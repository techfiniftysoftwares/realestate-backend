<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyInquiryController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ContactSubmissionController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\GeneralController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (require Passport authentication)
Route::middleware(['auth:api'])->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/signup', [AuthController::class, 'signup']);
    });

    // User routes
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'destroy']);
    Route::get('user/profile', [UserController::class, 'getProfile']);
    Route::put('user/profile', [UserController::class, 'updateProfile']);
    Route::put('users/{user}/edit', [UserController::class, 'updateUserSpecifics']);
    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

    // Role and module routes
    Route::apiResource('/roles', RoleController::class);
    Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions']);
    Route::get('/modules', [ModuleController::class, 'getModules']);
    Route::patch('/modules/{id}/toggle-status', [ModuleController::class, 'toggleModuleStatus']);
    Route::patch('/submodules/{id}/toggle-status', [ModuleController::class, 'toggleSubmoduleStatus']);
    Route::delete('modules/{id}', [ModuleController::class, 'destroyModule']);
    Route::delete('submodules/{id}', [ModuleController::class, 'destroySubmodule']);
    Route::post('modules', [ModuleController::class, 'storeModule']);
    Route::post('submodules', [ModuleController::class, 'storeSubmodule']);
    Route::get('submodules', [ModuleController::class, 'getSubmodules']);

    // Properties management (Admin)
    Route::get('properties', [PropertyController::class, 'adminIndex']);
    Route::post('properties', [PropertyController::class, 'store']);
    Route::get('properties/{property}', [PropertyController::class, 'adminShow']);
    Route::put('properties/{property}', [PropertyController::class, 'update']);
    Route::delete('properties/{property}', [PropertyController::class, 'destroy']);
    Route::post('properties/{property}/toggle-featured', [PropertyController::class, 'toggleFeatured']);
    Route::post('properties/bulk-update-status', [PropertyController::class, 'bulkUpdateStatus']);

    // Property inquiries management (Admin)
    Route::get('property-inquiries', [PropertyInquiryController::class, 'index']);
    Route::get('property-inquiries/{propertyInquiry}', [PropertyInquiryController::class, 'show']);
    Route::put('property-inquiries/{propertyInquiry}', [PropertyInquiryController::class, 'update']);
    Route::delete('property-inquiries/{propertyInquiry}', [PropertyInquiryController::class, 'destroy']);
    Route::post('property-inquiries/bulk-update', [PropertyInquiryController::class, 'bulkUpdateStatus']);
    Route::post('property-inquiries/{propertyInquiry}/schedule', [PropertyInquiryController::class, 'scheduleViewing']);
    Route::get('property-inquiries-stats', [PropertyInquiryController::class, 'getStats']);
    Route::get('upcoming-viewings', [PropertyInquiryController::class, 'getUpcomingViewings']);

    // Blog management (Admin)
    Route::get('blog', [BlogController::class, 'adminIndex']);
    Route::post('blog', [BlogController::class, 'store']);
    Route::get('blog/{blog}', [BlogController::class, 'show']);
    Route::put('blog/{blog}', [BlogController::class, 'update']);
    Route::delete('blog/{blog}', [BlogController::class, 'destroy']);

    // Contact submissions management (Admin)
    Route::apiResource('contact-submissions', ContactSubmissionController::class);

    // Newsletter management (Admin)
    Route::get('newsletter-subscribers', [NewsletterController::class, 'index']);
    Route::delete('newsletter-subscribers/{subscriber}', [NewsletterController::class, 'destroy']);

    // Analytics and dashboard (Admin)
    Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('analytics/property-views', [AnalyticsController::class, 'propertyViews']);
    Route::get('analytics/top-properties', [AnalyticsController::class, 'topProperties']);
});

// Public routes (no authentication required)
Route::prefix('public')->group(function () {

    // Properties (Public)
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/featured', [PropertyController::class, 'featured']);
    Route::get('properties/search', [PropertyController::class, 'search']);
    Route::get('properties/{slug}', [PropertyController::class, 'show']);

    // Blog (Public)
    Route::get('blog', [BlogController::class, 'index']);
    Route::get('blog/recent', [BlogController::class, 'recent']);
    Route::get('blog/{slug}', [BlogController::class, 'show']);

    // Contact and submissions (Public)
    Route::post('contact', [ContactController::class, 'submitContactForm']);
    Route::post('property-inquiry', [ContactController::class, 'submitPropertyInquiry']);
    Route::post('newsletter/subscribe', [ContactController::class, 'subscribeNewsletter']);
    Route::post('newsletter/unsubscribe', [ContactController::class, 'unsubscribeNewsletter']);

    // Property inquiry availability check (Public)
    Route::get('properties/{propertyId}/inquiry-availability', [PropertyInquiryController::class, 'checkAvailability']);

    // General pages (Public)
    Route::get('homepage', [GeneralController::class, 'homepage']);
    Route::get('about', [GeneralController::class, 'aboutPage']);
    Route::get('search-suggestions', [GeneralController::class, 'searchSuggestions']);
});
