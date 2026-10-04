<?php

namespace App\Http\Controllers;

use App\Jobs\SyncFilmCast;
use App\Models\CastMember;
use App\Models\Film;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class AdminFilmDetailController extends Controller
{
    public function show(Film $film): View
    {
        if ($film->tmdb_id && ! $film->castMembers()->exists()) {
            SyncFilmCast::dispatch($film->id)->afterResponse();
        }

        $film->load([
            'reviews' => fn ($query) => $query
                ->with('user')
                ->withCount([
                    'reactions as agree_count' => fn ($reactions) => $reactions->where('reaction', 'agree'),
                    'reactions as disagree_count' => fn ($reactions) => $reactions->where('reaction', 'disagree'),
                ]),
            'teasers',
            'castMembers',
        ])->loadAvg('reviews', 'rating');
        $cast = $film->castMembers->map(fn (CastMember $member): array => [
            'id' => $member->tmdb_id,
            'name' => $member->name,
            'character' => $member->pivot->character,
            'profile_url' => $member->filmProfileUrl(),
        ])->all();
        $userLists = auth()->user()->movieLists()
            ->where('is_official', false)
            ->withCount('films')
            ->withExists(['films as contains_film' => fn (Builder $query) => $query->whereKey($film->id)])
            ->get();
        $isFavorite = auth()->user()->favoriteFilms()->whereKey($film->id)->exists();

        return view('admin.films.show', compact('film', 'cast', 'userLists', 'isFavorite'));
    }
}
