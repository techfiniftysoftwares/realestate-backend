<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('username')->nullable()->after('last_name');
        });

        // Migrate existing data
        DB::table('users')->get()->each(function ($user) {
            $nameParts = explode(' ', $user->name, 2);

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'username' => $user->name, // Keep original name as username
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            // Make columns required after data migration
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('username')->unique()->nullable(false)->change();

            // Drop the old name column
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the name column
            $table->string('name')->nullable()->after('id');
        });

        // Migrate data back
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            // Make name required and drop new columns
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name', 'username']);
        });
    }
};
