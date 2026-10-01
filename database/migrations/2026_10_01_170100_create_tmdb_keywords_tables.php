<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmdb_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('name');
            $table->index('name');
            $table->timestamps();
        });

        Schema::create('film_tmdb_keyword', function (Blueprint $table) {
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tmdb_keyword_id')->constrained('tmdb_keywords')->cascadeOnDelete();
            $table->primary(['film_id', 'tmdb_keyword_id']);
            $table->index('tmdb_keyword_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_tmdb_keyword');
        Schema::dropIfExists('tmdb_keywords');
    }
};
