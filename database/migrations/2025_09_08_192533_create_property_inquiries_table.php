<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('visitor_name');
            $table->string('visitor_email');
            $table->string('visitor_phone')->nullable();
            $table->enum('inquiry_type', ['general', 'viewing', 'offer', 'information']);
            $table->text('message')->nullable();
            $table->datetime('preferred_viewing_date')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'completed', 'cancelled'])->default('pending');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('property_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_inquiries');
    }
};
