<?php

namespace App\Jobs;

use App\Models\Film;
use App\Services\CastMemberSyncService;
use App\Services\PublicMediaStorage;
use App\Services\TmdbService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncFilmCast implements ShouldQueue
{
    use Queueable;

    /** @param array<int, array<string, mixed>>|null $cast */
    public function __construct(
        public int $filmId,
        public ?array $cast = null,
        public ?string $posterPath = null,
        public ?string $previousPosterPath = null,
    ) {}

    public function handle(TmdbService $tmdb, CastMemberSyncService $castSync, PublicMediaStorage $mediaStorage): void
    {
        $film = Film::find($this->filmId);
        if (! $film) {
            return;
        }

        $posterPath = $this->posterPath;
        if ($posterPath) {
            $image = $tmdb->downloadPoster($posterPath);
            $extension = $image ? match ($image['content_type']) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/avif' => 'avif',
                'image/gif' => 'gif',
                default => null,
            } : null;

            if ($image && $extension) {
                $film->poster_path = $mediaStorage->storeContents(
                    $image['contents'],
                    'posters',
                    $extension,
                    $image['content_type'],
                );
                $film->save();
                $mediaStorage->delete($this->previousPosterPath);
            } elseif ($this->previousPosterPath) {
                $film->poster_path = $this->previousPosterPath;
                $film->save();
            }
        }

        if ($film->tmdb_id) {
            $cast = $this->cast ?? $tmdb->castMembers((int) $film->tmdb_id);
            $castSync->sync($film, $cast);
        }
    }
}
