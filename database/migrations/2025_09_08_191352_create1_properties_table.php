<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique();

            // Foreign Keys to lookup tables
            $table->foreignId('property_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_use_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_style_id')->nullable()->constrained()->onDelete('set null');

            // Status
            $table->enum('status', [
                'available',
                'sold',
                'pending',
                'draft',
                'rented'
            ])->default('available');

            // Listing Type
            $table->enum('listing_type', ['sale', 'rent', 'lease'])->default('sale');

            // Price
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('KES');



            // Property Details
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->decimal('area', 10, 2)->nullable(); // Square footage/meters
            $table->decimal('lot_size', 10, 2)->default(0); // Lot size
            $table->integer('year_built')->nullable();
            $table->integer('garage_spaces')->default(0); // Parking/garage spaces

            // Neighborhood ratings (JSON object)
            $table->json('neighborhood_ratings')->nullable(); // {schools: 9, restaurants: 8, transit: 6, shopping: 7}

            // Featured flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_best_deal')->default(false);

            // Virtual tour
            $table->string('virtual_tour_url')->nullable();

            // SEO and tracking
            $table->integer('view_count')->default(0);
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            // Agent relationship
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'property_type_id']);
            $table->index(['price', 'listing_type']);
            $table->index(['location_id', 'property_type_id']);
            $table->index(['is_featured', 'is_best_deal']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
