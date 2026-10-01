<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Models\MovieList;
use App\Models\TmdbKeyword;
use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MovieListController extends Controller
{
    public function __construct(private TmdbService $tmdb) {}

    public function index(Request $request)
    {
        $query = MovieList::where('is_official', true)
            ->with(['films' => fn ($films) => $films->select('films.id', 'films.title', 'films.poster_path')->orderByPivot('created_at')->orderByPivot('id')->limit(4)])
            ->withCount('films')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($matches) => $matches
                ->where('title', 'like', '%'.$request->input('q').'%')
                ->orWhere('description', 'like', '%'.$request->input('q').'%')
                ->orWhere('classification', 'like', '%'.$request->input('q').'%')))
            ->when(in_array($request->input('type'), ['genre', 'theme'], true), fn ($q) => $q->where('classification_type', $request->input('type')))
            ->when($request->filled('genre'), fn ($q) => $q->where('classification_type', 'genre')->where(fn ($match) => $match->whereJsonContains('classifications', $request->input('genre'))->orWhere('classification', $request->input('genre'))))
            ->when($request->filled('theme'), fn ($q) => $q->where('classification_type', 'theme')->where(fn ($match) => $match->whereJsonContains('classifications', $request->input('theme'))->orWhere('classification', $request->input('theme'))));
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['newest', 'oldest', 'most_films', 'alphabetical'], true) ? $sort : 'newest';
        match ($sort) {
            'oldest' => $query->oldest(),
            'most_films' => $query->orderByDesc('films_count')->orderBy('title'),
            'alphabetical' => $query->orderBy('title'),
            default => $query->latest(),
        };
        $lists = $query->paginate(12)->withQueryString();
        $genreClassifications = $this->genres();
        $themeClassifications = $this->themes();
        $totalFilms = DB::table('list_films')
            ->join('movie_lists', 'movie_lists.id', '=', 'list_films.movie_list_id')
            ->where('movie_lists.is_official', true)
            ->distinct()
            ->count('list_films.film_id');
        $stats = [
            'total' => MovieList::where('is_official', true)->count(),
            'genres' => MovieList::where('is_official', true)->where('classification_type', 'genre')->count(),
            'themes' => MovieList::where('is_official', true)->where('classification_type', 'theme')->count(),
            'films' => $totalFilms,
        ];

        return view('admin.lists.index', compact('lists', 'genreClassifications', 'themeClassifications', 'stats'));
    }

    public function create()
    {
        $genres = $this->genres();
        $themes = $this->themes();
        $list = new MovieList;
        $selectedFilmIds = old('film_ids', []);
        $selectedFilms = Film::whereIn('id', is_array($selectedFilmIds) ? $selectedFilmIds : [])->with('tmdbKeywords:id,tmdb_id,name')->get();
        $selectedFilmData = $this->filmPickerData($selectedFilms);
        $selectedClassifications = old('classifications', old('classification') ? [old('classification')] : []);
        $selectedClassifications = is_array($selectedClassifications) ? $selectedClassifications : [];

        return view('admin.lists.create', compact('genres', 'themes', 'list', 'selectedFilmData', 'selectedClassifications'));
    }

    public function store(Request $request)
    {
        $request->merge(['classifications' => $request->input('classifications', array_filter([$request->input('classification')]))]);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classification_type' => ['required', Rule::in(['genre', 'theme'])],
            'classifications' => 'required|array|min:1|max:20',
            'classifications.*' => 'required|string|max:120|distinct',
            'is_featured' => 'nullable|boolean',
            'film_ids' => 'nullable|array|max:1000',
            'film_ids.*' => 'required|integer|distinct|exists:films,id',
        ]);
        $this->validateClassification($request, $validated);
        $list = DB::transaction(function () use ($validated, $request) {
            $list = MovieList::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_public' => true,
                'is_official' => true,
                'is_featured' => $request->boolean('is_featured'),
                'classification_type' => $validated['classification_type'],
                'classification' => $validated['classifications'][0],
                'classifications' => array_values($validated['classifications']),
            ]);

            $list->films()->sync($validated['film_ids'] ?? []);

            return $list;
        });

        return redirect()->route('admin.lists.show', $list)->with('status', 'Official list created successfully.');
    }

    public function edit(MovieList $list)
    {
        abort_unless($list->is_official, 404);

        $genres = $this->genres();
        $themes = $this->themes();
        $selectedFilmIds = old('film_ids');
        $selectedFilms = is_array($selectedFilmIds)
            ? Film::whereIn('id', $selectedFilmIds)->with('tmdbKeywords:id,tmdb_id,name')->withCount('reviews')->withAvg('reviews', 'rating')->get()
            : $list->films()->with('tmdbKeywords:id,tmdb_id,name')->withCount('reviews')->withAvg('reviews', 'rating')->get();
        $selectedFilmData = $this->filmPickerData($selectedFilms);
        $selectedClassifications = old('classifications', $list->classifications ?: array_filter([$list->classification]));
        $selectedClassifications = is_array($selectedClassifications) ? $selectedClassifications : [];

        return view('admin.lists.edit', compact('list', 'genres', 'themes', 'selectedFilmData', 'selectedClassifications'));
    }

    public function update(Request $request, MovieList $list)
    {
        abort_unless($list->is_official, 404);

        $request->merge(['classifications' => $request->input('classifications', array_filter([$request->input('classification')]))]);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'classification_type' => ['required', Rule::in(['genre', 'theme'])],
            'classifications' => 'required|array|min:1|max:20',
            'classifications.*' => 'required|string|max:120|distinct',
            'is_featured' => 'nullable|boolean',
            'film_ids' => 'nullable|array|max:1000',
            'film_ids.*' => 'required|integer|distinct|exists:films,id',
        ]);
        $this->validateClassification($request, $validated);

        DB::transaction(function () use ($list, $validated, $request) {
            $list->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_featured' => $request->boolean('is_featured'),
                'classification_type' => $validated['classification_type'],
                'classification' => $validated['classifications'][0],
                'classifications' => array_values($validated['classifications']),
            ]);
            $list->films()->sync($validated['film_ids'] ?? []);
        });

        return redirect()->route('admin.lists.show', $list)->with('status', 'Official list updated successfully.');
    }

    public function destroy(MovieList $list)
    {
        abort_unless($list->is_official, 404);

        DB::transaction(function () use ($list) {
            $list->films()->detach();
            $list->delete();
        });

        return redirect()->route('admin.lists.index')->with('status', 'List deleted successfully.');
    }

    public function show(MovieList $list)
    {
        abort_unless($list->is_official, 404);
        $list->loadCount('films');
        $previewFilms = $list->films()->orderByPivot('created_at')->orderByPivot('id')->limit(4)->get();
        $currentFilms = $list->films()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByPivot('created_at')
            ->orderByPivot('id')
            ->paginate(24, ['films.*'], 'films_page')
            ->withQueryString();

        return view('admin.lists.show', compact('list', 'currentFilms', 'previewFilms'));
    }

    public function manageFilms(Request $request, MovieList $list)
    {
        abort_unless($list->is_official, 404);

        $list->loadCount('films');
        $selectedFilmIds = old('film_ids');
        $selectedFilms = is_array($selectedFilmIds)
            ? Film::whereIn('id', $selectedFilmIds)->with('tmdbKeywords:id,tmdb_id,name')->withCount('reviews')->withAvg('reviews', 'rating')->get()
            : $list->films()->with('tmdbKeywords:id,tmdb_id,name')->withCount('reviews')->withAvg('reviews', 'rating')->get();
        $selectedFilmData = $this->filmPickerData($selectedFilms);
        $currentFilms = $list->films()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByPivot('created_at')
            ->orderByPivot('id')
            ->paginate(20, ['films.*'], 'current_page')
            ->withQueryString();
        $search = (string) request('q', '');
        $genres = $this->genres();
        $themes = $this->themes();

        return view('admin.lists.manage-films', compact('list', 'currentFilms', 'selectedFilmData', 'search', 'genres', 'themes'));
    }

    public function matchingFilms(Request $request)
    {
        $validated = $request->validate([
            'classification_type' => ['nullable', Rule::in(['genre', 'theme'])],
            'classification' => 'nullable|string|max:120',
            'classifications' => 'nullable|array|max:20',
            'classifications.*' => 'required|string|max:120|distinct',
            'q' => 'nullable|string|min:2|max:120',
            'list_id' => 'nullable|integer|exists:movie_lists,id',
            'matching' => 'nullable|boolean',
        ]);

        $list = isset($validated['list_id']) ? MovieList::findOrFail($validated['list_id']) : null;
        abort_if($list && ! $list->is_official, 404);

        $filmsQuery = Film::query()
            ->select(['id', 'tmdb_id', 'title', 'original_title', 'genre', 'release_year', 'release_date', 'poster_path'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with('tmdbKeywords:id,tmdb_id,name')
            ->when(filled($validated['q'] ?? null), function ($query) use ($validated) {
                $term = trim($validated['q']);
                $query->where(function ($matches) use ($term) {
                    $matches->where('title', 'like', '%'.$term.'%')
                        ->orWhere('original_title', 'like', '%'.$term.'%');
                    if (ctype_digit($term)) {
                        $matches->orWhere('tmdb_id', (int) $term);
                    }
                });
            });

        $classifications = $validated['classifications'] ?? (filled($validated['classification'] ?? null) ? [$validated['classification']] : []);
        $matchingOnly = $validated['matching'] ?? false;
        if ($matchingOnly) {
            $matchingFilmIds = Film::query()->select('films.id');
            if (($validated['classification_type'] ?? null) === 'genre' && $classifications !== []) {
                $matchingFilmIds->where(function ($matches) use ($classifications) {
                    foreach ($classifications as $genre) {
                        $matches->orWhere('genre', $genre)
                            ->orWhere('genre', 'like', $genre.',%')
                            ->orWhere('genre', 'like', '%, '.$genre.',%')
                            ->orWhere('genre', 'like', '%,'.$genre.',%')
                            ->orWhere('genre', 'like', '%, '.$genre)
                            ->orWhere('genre', 'like', '%,'.$genre);
                    }
                });
            } elseif (($validated['classification_type'] ?? null) === 'theme' && $classifications !== []) {
                $matchingFilmIds->where(fn ($matches) => $matches
                    ->whereHas('tmdbKeywords', fn ($keywords) => $keywords->whereIn('tmdb_keywords.name', $classifications))
                    ->orWhereHas('movieLists', function ($lists) use ($classifications) {
                        $lists->where('movie_lists.is_official', true)->where('movie_lists.classification_type', 'theme')
                            ->where(function ($themeMatches) use ($classifications) {
                                $themeMatches->whereIn('movie_lists.classification', $classifications);
                                foreach ($classifications as $theme) {
                                    $themeMatches->orWhereJsonContains('movie_lists.classifications', $theme);
                                }
                            });
                    }));
            } elseif ($classifications !== []) {
                $matchingFilmIds->whereRaw('1 = 0');
            }

            $filmsQuery->whereIn('films.id', $matchingFilmIds);
        }

        $filmsPage = $filmsQuery->orderBy('title')->paginate(48)->appends($request->query());

        return response()->json([
            'films' => $this->filmPickerData($filmsPage->getCollection()),
            'meta' => ['current_page' => $filmsPage->currentPage(), 'last_page' => $filmsPage->lastPage(), 'total' => $filmsPage->total()],
        ]);
    }

    public function movieMetadata(Film $film)
    {
        if (! $film->tmdb_id) {
            return response()->json(['available' => false, 'message' => 'TMDB metadata unavailable.']);
        }

        $metadata = $this->tmdb->movieMetadata((int) $film->tmdb_id);
        if (! ($metadata['available'] ?? false)) {
            return response()->json(['available' => false, 'message' => 'TMDB metadata unavailable.']);
        }

        $film->syncTmdbKeywords($metadata['keywords'] ?? []);

        return response()->json([
            'available' => true,
            'genres' => $metadata['genres'] ?? [],
            'keywords' => $metadata['keywords'] ?? [],
        ]);
    }

    public function searchFilms(Request $request, MovieList $list)
    {
        abort_unless($list->is_official, 404);

        return redirect()->route('admin.lists.manage-films', array_filter(['list' => $list->id, 'q' => $request->input('q')]));
    }

    public function addFilm(Request $request, MovieList $list)
    {
        abort_unless($list->is_official, 404);
        $validated = $request->validate([
            'film_id' => 'nullable|required_without:film_ids|integer|exists:films,id',
            'film_ids' => 'nullable|required_without:film_id|array|min:1|max:100',
            'film_ids.*' => 'required|integer|distinct|exists:films,id',
        ]);
        $filmIds = isset($validated['film_ids']) ? $validated['film_ids'] : [$validated['film_id']];
        $newFilmIds = array_values(array_diff($filmIds, $list->films()->pluck('films.id')->all()));

        DB::transaction(function () use ($list, $newFilmIds) {
            $list->films()->syncWithoutDetaching($newFilmIds);
            if ($newFilmIds !== []) {
                $list->touch();
            }
        });

        return back()->with('status', count($newFilmIds).' film'.(count($newFilmIds) === 1 ? '' : 's').' added to list.');
    }

    public function syncFilms(Request $request, MovieList $list)
    {
        abort_unless($list->is_official, 404);
        $validated = $request->validate([
            'film_ids' => 'nullable|array|max:1000',
            'film_ids.*' => 'required|integer|distinct|exists:films,id',
        ]);

        DB::transaction(function () use ($list, $validated) {
            $list->films()->sync($validated['film_ids'] ?? []);
            $list->touch();
        });

        return back()->with('status', 'Movie selection saved.');
    }

    public function removeFilm(MovieList $list, Film $film)
    {
        abort_unless($list->is_official, 404);
        DB::transaction(function () use ($list, $film) {
            $list->films()->detach($film->id);
            $list->touch();
        });

        return back()->with('status', 'Movie removed from list.');
    }

    private function genres()
    {
        return Film::whereNotNull('genre')->where('genre', '!=', '')->pluck('genre')
            ->flatMap(fn (string $genres) => explode(',', $genres))
            ->map(fn (string $genre) => trim($genre))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function themes()
    {
        return MovieList::where('is_official', true)->where('classification_type', 'theme')->whereNotNull('classification')->whereHas('films')->distinct()->pluck('classification')
            ->merge(TmdbKeyword::query()->whereHas('films')->orderBy('name')->pluck('name'))
            ->unique()
            ->sort()
            ->values();
    }

    private function filmPickerData(Collection $films): array
    {
        return $films->map(fn (Film $film): array => [
            'id' => $film->id,
            'tmdb_id' => $film->tmdb_id,
            'title' => $film->title,
            'original_title' => $film->original_title,
            'genre' => $film->genre,
            'year' => $film->release_year ?: $film->release_date?->format('Y'),
            'rating' => $film->reviews_count > 0 ? number_format((float) $film->reviews_avg_rating, 1) : null,
            'poster_url' => $film->posterUrl(),
            'tmdb_keywords' => $film->relationLoaded('tmdbKeywords') ? $film->tmdbKeywords->pluck('name')->values()->all() : [],
        ])->values()->all();
    }

    private function validateClassification(Request $request, array $validated): void
    {
        if ($validated['classification_type'] === 'genre') {
            $request->validate([
                'classifications' => 'required|array|min:1|max:20',
                'classifications.*' => ['required', Rule::in($this->genres()->all())],
            ]);
        } else {
            $request->validate([
                'classifications' => 'required|array|min:1|max:20',
                'classifications.*' => ['required', Rule::in($this->themes()->all())],
            ]);
        }
    }
}
