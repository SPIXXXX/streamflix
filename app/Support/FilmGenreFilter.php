<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class FilmGenreFilter
{
    /** @param array<int, string> $genres */
    public static function apply(Builder $query, array $genres): Builder
    {
        if ($genres === []) {
            return $query;
        }

        return $query->where(function (Builder $matches) use ($genres): void {
            foreach ($genres as $genre) {
                $matches->orWhere(function (Builder $exactGenre) use ($genre): void {
                    $exactGenre->where('genre', $genre)
                        ->orWhere('genre', 'like', $genre.',%')
                        ->orWhere('genre', 'like', $genre.', %')
                        ->orWhere('genre', 'like', '%,'.$genre.',%')
                        ->orWhere('genre', 'like', '%, '.$genre.',%')
                        ->orWhere('genre', 'like', '%,'.$genre)
                        ->orWhere('genre', 'like', '%, '.$genre);
                });
            }
        });
    }
}
