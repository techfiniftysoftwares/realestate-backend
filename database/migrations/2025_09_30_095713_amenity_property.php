<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenity_property', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['property_id', 'amenity_id']);
            $table->index('property_id');
            $table->index('amenity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_property');
    }
};
