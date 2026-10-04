<?php

namespace App\Services;

use App\Models\CastMember;
use App\Models\Film;

class CastMemberSyncService
{
    public function __construct(
        private TmdbService $tmdb,
        private PublicMediaStorage $mediaStorage,
    ) {}

    /** @param array<int, array<string, mixed>> $members */
    public function sync(Film $film, array $members): void
    {
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

            if ($profilePath && (! $castMember->profile_path || str_contains((string) $castMember->profile_path, 'image.tmdb.org'))) {
                $image = $this->tmdb->downloadPoster($profilePath, 'w185');
                $extension = $image ? match ($image['content_type']) {
                    'image/jpeg', 'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/avif' => 'avif',
                    'image/gif' => 'gif',
                    default => null,
                } : null;

                if ($image && $extension) {
                    $castMember->profile_path = $this->mediaStorage->storeContents(
                        $image['contents'],
                        'cast',
                        $extension,
                        $image['content_type'],
                    );
                } else {
                    $castMember->profile_path = $this->tmdb->posterUrl($profilePath, 'w185');
                }
            }

            $castMember->save();
            $cast[$castMember->id] = [
                'character' => $member['character'] ?? null,
                'cast_order' => (int) ($member['order'] ?? $index),
            ];
        }

        $film->castMembers()->sync($cast);
    }
}
