<?php

namespace App\Providers;

use App\Models\Property;
use App\Observers\PropertyObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services and bindings
        $this->app->singleton(\App\Services\PropertyService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // Register model observers
        Property::observe(PropertyObserver::class);

        // Register custom validation rules (if needed)
        // Validator::extend('kenyan_phone', function ($attribute, $value, $parameters, $validator) {
        //     return preg_match('/^(?:254|\+254|0)([71](?:(?:[0-9]){8}))$/', $value);
        // });
    }
}
