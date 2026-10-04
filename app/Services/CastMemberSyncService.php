<?php

namespace App\Services;

use App\Models\CastMember;
use App\Models\Film;

class CastMemberSyncService
{
    public function __construct(
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
            if ($profilePath) {
                $castMember->profile_path = $profilePath;
            }

            $castMember->save();
            $cast[$castMember->id] = [
                'character' => $member['character'] ?? null,
                'cast_order' => (int) ($member['order'] ?? $index),
                'tmdb_profile_path' => $profilePath,
                'profile_path' => null,
            ];
        }

        $film->castMembers()->sync($cast);

        foreach ($existingCast as $memberId => $member) {
            $oldProfilePath = $member->pivot->profile_path;
            if ($oldProfilePath && ($cast[$memberId]['profile_path'] ?? null) !== $oldProfilePath) {
                $this->mediaStorage->delete($oldProfilePath);
            }
        }
    }
}
