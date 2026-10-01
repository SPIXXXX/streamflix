<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Services\TmdbService;
use Illuminate\View\View;

class AdminFilmDetailController extends Controller
{
    public function show(Film $film, TmdbService $tmdb): View
    {
        $film->load([
            'reviews' => fn ($query) => $query
                ->with('user')
                ->withCount([
                    'reactions as agree_count' => fn ($reactions) => $reactions->where('reaction', 'agree'),
                    'reactions as disagree_count' => fn ($reactions) => $reactions->where('reaction', 'disagree'),
                ]),
            'teasers',
        ])->loadAvg('reviews', 'rating');
        $cast = $film->tmdb_id ? $tmdb->castMembers((int) $film->tmdb_id) : [];
        $userLists = auth()->user()->movieLists()->where('is_official', false)->withCount('films')->get();
        $isFavorite = auth()->user()->favoriteFilms()->whereKey($film->id)->exists();

        return view('admin.films.show', compact('film', 'cast', 'userLists', 'isFavorite'));
    }
}
