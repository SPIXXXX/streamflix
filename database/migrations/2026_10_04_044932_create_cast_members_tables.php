<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cast_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('name');
            $table->text('biography')->nullable();
            $table->date('birthday')->nullable();
            $table->date('deathday')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('known_for_department')->nullable();
            $table->text('profile_path')->nullable();
            $table->timestamp('biography_fetched_at')->nullable();
            $table->timestamps();
        });

        Schema::create('film_cast', function (Blueprint $table) {
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cast_member_id')->constrained('cast_members')->cascadeOnDelete();
            $table->string('character')->nullable();
            $table->unsignedSmallInteger('cast_order')->default(0);
            $table->timestamps();
            $table->primary(['film_id', 'cast_member_id']);
            $table->index('cast_member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_cast');
        Schema::dropIfExists('cast_members');
    }
};
