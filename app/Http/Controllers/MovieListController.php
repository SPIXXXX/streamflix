<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\MovieList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MovieListController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $previewFilms = fn ($query) => $query
            ->select('films.id', 'films.title', 'films.poster_path')
            ->orderBy('list_films.created_at', 'desc')
            ->orderBy('list_films.id', 'desc')
            ->limit(4);
        $listCardRelations = ['user', 'films' => $previewFilms];

        $favoriteCount = $user?->favoriteFilms()->count() ?? 0;
        $favoritePreviews = $user?->favoriteFilms()
            ->select('films.id', 'films.title', 'films.poster_path')
            ->orderBy('film_favorites.created_at', 'desc')
            ->orderBy('film_favorites.id', 'desc')
            ->limit(4)
            ->get() ?? collect();
        $favoriteList = new MovieList([
            'title' => 'Favorites',
            'description' => 'Movies you\'ve saved as favorites.',
            'is_public' => false,
        ]);
        $favoriteList->setAttribute('films_count', $favoriteCount);
        $myLists = $user?->movieLists()
            ->withCount('films')
            ->with($listCardRelations)
            ->latest()
            ->get() ?? collect();

        $featured = MovieList::where('is_official', true)
            ->where('is_featured', true)
            ->where('is_public', true)
            ->withCount('films')
            ->with($listCardRelations)
            ->latest()
            ->take(6)
            ->get();

        $recentlyPopular = MovieList::where('is_public', true)
            ->withCount('films')
            ->with($listCardRelations)
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('films_count')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $crewPicks = MovieList::where('is_official', true)
            ->where('is_public', true)
            ->where('is_featured', false)
            ->withCount('films')
            ->with($listCardRelations)
            ->latest()
            ->take(6)
            ->get();

        $publicLists = MovieList::where('is_public', true)
            ->when($user, fn ($query) => $query->where('user_id', '!=', $user->id))
            ->withCount('films')
            ->with($listCardRelations)
            ->latest()
            ->paginate(12);

        return view('lists.index', compact(
            'favoriteCount',
            'favoritePreviews',
            'favoriteList',
            'myLists',
            'featured',
            'recentlyPopular',
            'crewPicks',
            'publicLists',
        ));
    }

    public function create()
    {
        return view('lists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        $list = Auth::user()->movieLists()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('lists.show', $list)->with('status', 'List created!');
    }

    public function favorites(): View
    {
        $user = Auth::user();
        $films = $user->favoriteFilms()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('film_favorites.created_at', 'desc')
            ->get();

        $list = new MovieList([
            'title' => 'Favorites',
            'description' => 'Movies you have saved as favorites.',
            'is_public' => false,
        ]);
        $list->setRelation('user', $user);
        $list->setRelation('films', $films);
        $list->setAttribute('films_count', $films->count());

        return view('lists.show', [
            'list' => $list,
            'allFilms' => collect(),
            'isFavoritesCollection' => true,
            'favoriteFilmIds' => $films->modelKeys(),
        ]);
    }

    public function show(MovieList $list): View
    {
        if (! $list->is_public && $list->user_id !== Auth::id()) {
            abort(403);
        }

        $list->loadCount('films');
        $list->load([
            'films' => fn ($query) => $query->withCount('reviews')->withAvg('reviews', 'rating'),
            'user',
        ]);
        $isOwner = Auth::check() && $list->user_id === Auth::id();
        $allFilms = $isOwner
            ? Film::whereNotIn('id', $list->films->modelKeys())->orderBy('title')->get()
            : collect();
        $favoriteFilmIds = Auth::user()?->favoriteFilms()
            ->whereIn('films.id', $list->films->modelKeys())
            ->pluck('films.id')
            ->all() ?? [];

        return view('lists.show', compact('list', 'allFilms', 'favoriteFilmIds') + ['isFavoritesCollection' => false]);
    }

    public function edit(MovieList $list)
    {
        abort_if($list->is_official, 403);
        abort_unless($list->user_id === Auth::id(), 403);

        return view('lists.edit', compact('list'));
    }

    public function update(Request $request, MovieList $list)
    {
        abort_if($list->is_official, 403);
        abort_unless($list->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        $list->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('lists.show', $list)->with('status', 'List updated!');
    }

    public function destroy(MovieList $list)
    {
        abort_if($list->is_official, 403);
        abort_unless($list->user_id === Auth::id(), 403);

        $list->delete();

        return redirect()->route('lists.index')->with('status', 'List deleted.');
    }

    public function addFilm(Request $request, MovieList $list)
    {
        abort_if($list->is_official, 403);
        abort_unless($list->user_id === Auth::id(), 403);

        $validated = $request->validate(['film_id' => 'required|exists:films,id']);

        $list->films()->syncWithoutDetaching([$validated['film_id']]);
        $list->touch();

        return back()->with('status', 'Film added to list.');
    }

    public function removeFilm(MovieList $list, Film $film)
    {
        abort_if($list->is_official, 403);
        abort_unless($list->user_id === Auth::id(), 403);

        $list->films()->detach($film->id);
        $list->touch();

        return back()->with('status', 'Film removed from list.');
    }
}
