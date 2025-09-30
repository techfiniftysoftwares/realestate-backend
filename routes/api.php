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


use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\PropertyTypeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes
Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/request', [AuthController::class, 'requestPasswordReset']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
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






    // Property Management (Admin/Agent)
    Route::prefix('properties')->group(function () {
        Route::post('/', [PropertyController::class, 'store']);
        Route::put('/{id}', [PropertyController::class, 'update']);
        Route::delete('/{id}', [PropertyController::class, 'destroy']);
    });

    // Favorites Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']); // Get all favorites
        Route::post('/', [FavoriteController::class, 'store']); // Add to favorites
        Route::post('/toggle', [FavoriteController::class, 'toggle']); // Toggle favorite
        Route::delete('/{propertyId}', [FavoriteController::class, 'destroy']); // Remove from favorites
        Route::get('/check/{propertyId}', [FavoriteController::class, 'check']); // Check if favorited
        Route::get('/count', [FavoriteController::class, 'count']); // Get count
        Route::delete('/', [FavoriteController::class, 'clear']); // Clear all
    });


    Route::prefix('contact-submissions')->group(function () {
        Route::get('/', [ContactSubmissionController::class, 'index']);
        Route::get('/{contactSubmission}', [ContactSubmissionController::class, 'show']);
        Route::put('/{contactSubmission}', [ContactSubmissionController::class, 'update']);
        Route::delete('/{contactSubmission}', [ContactSubmissionController::class, 'destroy']);
    });

    Route::post('/property-types', [PropertyTypeController::class, 'store']);
    Route::put('/property-types/{id}', [PropertyTypeController::class, 'update']);
    Route::delete('/property-types/{id}', [PropertyTypeController::class, 'destroy']);
    Route::patch('/property-types/{id}/toggle', [PropertyTypeController::class, 'toggleActive']);
    Route::post('/property-types/reorder', [PropertyTypeController::class, 'reorder']);











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







    // Newsletter management (Admin)
    Route::get('newsletter-subscribers', [NewsletterController::class, 'index']);
    Route::delete('newsletter-subscribers/{subscriber}', [NewsletterController::class, 'destroy']);

    // Analytics and dashboard (Admin)
    Route::get('dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('analytics/property-views', [AnalyticsController::class, 'propertyViews']);
    Route::get('analytics/top-properties', [AnalyticsController::class, 'topProperties']);
});






// Public contact route (no auth required)
Route::post('/contact-submissions', [ContactSubmissionController::class, 'store']);



// Public property types (no auth required)
Route::get('/property-types', [PropertyTypeController::class, 'index']);
Route::get('/property-types/{id}', [PropertyTypeController::class, 'show']);




// Public routes (no authentication required)

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


// Public Property Routes
Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index']); // List with filters
    Route::get('/featured', [PropertyController::class, 'featured']); // Featured properties
    Route::get('/best-deals', [PropertyController::class, 'bestDeals']); // Best deals
    Route::get('/{slug}', [PropertyController::class, 'show']); // Single property
});

// Filter Options Routes (for dropdown data)
Route::prefix('filters')->group(function () {
    Route::get('/all', [FilterController::class, 'getAllFilters']); // Get all in one request
    Route::get('/types', [FilterController::class, 'getPropertyTypes']);
    Route::get('/locations', [FilterController::class, 'getLocations']);
    Route::get('/use-categories', [FilterController::class, 'getUseCategories']);
    Route::get('/styles', [FilterController::class, 'getStyles']);
    Route::get('/amenities', [FilterController::class, 'getAmenities']);
});
