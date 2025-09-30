<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->string('preferred_contact')->default('email')->after('message');
            $table->foreignId('property_type_id')->nullable()->constrained('property_types')->after('preferred_contact');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropForeign(['property_type_id']);
            $table->dropColumn(['preferred_contact', 'property_type_id']);
        });
    }
};
