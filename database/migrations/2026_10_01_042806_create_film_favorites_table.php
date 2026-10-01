<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('film_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'film_id']);
        });

        DB::table('movie_lists as lists')
            ->join('list_films as entries', 'entries.movie_list_id', '=', 'lists.id')
            ->where('lists.title', 'Favorites')
            ->where('lists.is_official', false)
            ->select('entries.id as entry_id', 'lists.user_id', 'entries.film_id')
            ->orderBy('entries.id')
            ->chunkById(500, function ($entries): void {
                $timestamp = now();
                $favorites = $entries->map(fn ($entry): array => [
                    'user_id' => $entry->user_id,
                    'film_id' => $entry->film_id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                DB::table('film_favorites')->insertOrIgnore($favorites);
            }, 'entries.id', 'entry_id');

        DB::table('movie_lists')
            ->where('title', 'Favorites')
            ->where('is_official', false)
            ->where('is_public', false)
            ->where('description', 'Films saved as favorites.')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('film_favorites')
            ->select('id', 'user_id', 'film_id')
            ->orderBy('id')
            ->chunkById(500, function ($favorites): void {
                foreach ($favorites as $favorite) {
                    $timestamp = now();
                    $listId = DB::table('movie_lists')->where([
                        'user_id' => $favorite->user_id,
                        'title' => 'Favorites',
                    ])->value('id');

                    if (! $listId) {
                        $listId = DB::table('movie_lists')->insertGetId([
                            'user_id' => $favorite->user_id,
                            'title' => 'Favorites',
                            'description' => 'Films saved as favorites.',
                            'is_public' => false,
                            'is_official' => false,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                    }

                    DB::table('list_films')->insertOrIgnore([
                        'movie_list_id' => $listId,
                        'film_id' => $favorite->film_id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }, 'film_favorites.id');

        Schema::dropIfExists('film_favorites');
    }
};
