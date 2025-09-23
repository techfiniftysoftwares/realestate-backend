<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at');

            $table->index(['property_id', 'viewed_at']);
            $table->index(['ip_address', 'viewed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_views');
    }
};
