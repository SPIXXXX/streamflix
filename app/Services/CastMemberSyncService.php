<?php

namespace App\Services;

use App\Models\CastMember;
use App\Models\Film;
use Illuminate\Support\Str;

class CastMemberSyncService
{
    public function __construct(
        private TmdbService $tmdb,
        private PublicMediaStorage $mediaStorage,
    ) {}

    /** @param array<int, array<string, mixed>> $members */
    public function sync(Film $film, array $members): void
    {
        $existingCast = $film->castMembers()->get()->keyBy('id');
        $cast = [];

        foreach (collect($members)->sortBy('order')->take(12)->values() as $index => $member) {
            $tmdbId = (int) ($member['id'] ?? 0);
            $name = trim((string) ($member['name'] ?? ''));
            if ($tmdbId < 1 || $name === '') {
                continue;
            }

            $castMember = CastMember::firstOrNew(['tmdb_id' => $tmdbId]);
            $castMember->name = $name;
            $profilePath = $member['profile_path'] ?? null;
            if (! $castMember->profile_path && $profilePath) {
                $castMember->profile_path = $profilePath;
            }

            $castMember->save();
            $existingFilmCast = $existingCast->get($castMember->id);
            $sameSource = $existingFilmCast?->pivot->tmdb_profile_path === $profilePath;
            $cast[$castMember->id] = [
                'character' => $member['character'] ?? null,
                'cast_order' => (int) ($member['order'] ?? $index),
                'tmdb_profile_path' => $profilePath,
                'profile_path' => $sameSource ? $existingFilmCast->pivot->profile_path : null,
            ];
        }

        $film->castMembers()->sync($cast);

        foreach ($existingCast as $memberId => $member) {
            $oldProfilePath = $member->pivot->profile_path;
            if ($oldProfilePath && ($cast[$memberId]['profile_path'] ?? null) !== $oldProfilePath) {
                $this->mediaStorage->delete($oldProfilePath);
            }
        }

        $this->storeFilmCastPhotos($film);
    }

    private function storeFilmCastPhotos(Film $film): void
    {
        $directory = 'cast/films/'.$film->id.'-'.Str::slug($film->title);

        foreach ($film->castMembers()->get() as $member) {
            $sourcePath = $member->pivot->tmdb_profile_path;
            if (! $sourcePath || $member->pivot->profile_path) {
                continue;
            }

            $image = $this->tmdb->downloadPoster($sourcePath, 'w185');
            $extension = $image ? match ($image['content_type']) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/avif' => 'avif',
                'image/gif' => 'gif',
                default => null,
            } : null;

            $profilePath = $image && $extension
                ? $this->mediaStorage->storeContents($image['contents'], $directory, $extension, $image['content_type'])
                : $this->tmdb->posterUrl($sourcePath, 'w185');

            if ($profilePath) {
                $film->castMembers()->updateExistingPivot($member->id, ['profile_path' => $profilePath]);
            }
        }
    }
}
