<?php

namespace App\Models;

use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Film extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id', 'title', 'original_title', 'synopsis', 'genre', 'release_date', 'release_year', 'poster_path', 'cast',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function teasers(): HasMany
    {
        return $this->hasMany(Teaser::class);
    }

    public function movieLists(): BelongsToMany
    {
        return $this->belongsToMany(MovieList::class, 'list_films')->withTimestamps();
    }

    public function tmdbKeywords(): BelongsToMany
    {
        return $this->belongsToMany(TmdbKeyword::class, 'film_tmdb_keyword');
    }

    public function castMembers(): BelongsToMany
    {
        return $this->belongsToMany(CastMember::class, 'film_cast')
            ->withPivot(['character', 'cast_order', 'tmdb_profile_path', 'profile_path'])
            ->orderByPivot('cast_order')
            ->withTimestamps();
    }

    public function syncTmdbKeywords(array $keywords): void
    {
        DB::transaction(function () use ($keywords): void {
            $keywordIds = collect($keywords)->map(function (array $keyword): ?int {
                if (empty($keyword['id']) || empty($keyword['name'])) {
                    return null;
                }

                return TmdbKeyword::updateOrCreate(
                    ['tmdb_id' => (int) $keyword['id']],
                    ['name' => (string) $keyword['name']],
                )->id;
            })->filter()->values()->all();

            $this->tmdbKeywords()->sync($keywordIds);
        });
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'film_favorites')->withTimestamps();
    }

    public function averageRating(): float
    {
        return (float) $this->reviews()->avg('rating');
    }

    public function posterUrl(): ?string
    {
        $posterPath = trim((string) $this->poster_path);

        if ($posterPath === '') {
            return null;
        }

        if (filter_var($posterPath, FILTER_VALIDATE_URL)) {
            return $posterPath;
        }

        if (str_starts_with($posterPath, '/')) {
            return app(TmdbService::class)->posterUrl($posterPath, 'w500');
        }

        return Storage::disk('public')->exists($posterPath)
            ? Storage::disk('public')->url($posterPath)
            : null;
    }
}
