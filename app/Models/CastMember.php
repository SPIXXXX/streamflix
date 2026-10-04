<?php

namespace App\Models;

use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class CastMember extends Model
{
    protected $fillable = [
        'tmdb_id', 'name', 'biography', 'birthday', 'deathday', 'place_of_birth',
        'known_for_department', 'profile_path', 'biography_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'deathday' => 'date',
            'biography_fetched_at' => 'datetime',
        ];
    }

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'film_cast')
            ->withPivot(['character', 'cast_order', 'tmdb_profile_path', 'profile_path'])
            ->withTimestamps();
    }

    public function profileUrl(): ?string
    {
        $path = trim((string) $this->profile_path);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return app(TmdbService::class)->posterUrl($path, 'w185');
        }

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }

    public function filmProfileUrl(): ?string
    {
        $tmdbPath = trim((string) $this->pivot?->tmdb_profile_path);

        return $tmdbPath !== ''
            ? app(TmdbService::class)->posterUrl($tmdbPath, 'w185')
            : $this->profileUrl();
    }
}
