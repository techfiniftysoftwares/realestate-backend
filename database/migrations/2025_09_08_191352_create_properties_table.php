<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['residential', 'commercial', 'land']);
            $table->enum('status', ['active', 'sold', 'pending', 'draft'])->default('active');
            $table->enum('listing_type', ['sale', 'rent', 'lease']);
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('KSh');

            // Location details
            $table->string('address');
            $table->string('city');
            $table->string('county');
            $table->string('country')->default('Kenya');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Property details
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('square_footage', 10, 2)->nullable();
            $table->decimal('lot_size', 10, 2)->nullable();
            $table->integer('year_built')->nullable();
            $table->integer('parking_spaces')->nullable();

            // Features and amenities (JSON)
            $table->json('amenities')->nullable();
            $table->json('features')->nullable();

            // Virtual tour
            $table->string('virtual_tour_url')->nullable();

            // SEO and display
            $table->string('slug')->unique();
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);


            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['price', 'listing_type']);
            $table->index(['city', 'county']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
