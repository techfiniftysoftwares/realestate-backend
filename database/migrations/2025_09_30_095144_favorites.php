<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user can only favorite a property once
            $table->unique(['user_id', 'property_id']);
            $table->index('user_id');
            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
