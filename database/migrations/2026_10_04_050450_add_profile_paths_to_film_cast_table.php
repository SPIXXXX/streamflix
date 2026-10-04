<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('film_cast', function (Blueprint $table) {
            $table->text('tmdb_profile_path')->nullable();
            $table->text('profile_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('film_cast', function (Blueprint $table) {
            $table->dropColumn(['tmdb_profile_path', 'profile_path']);
        });
    }
};
