<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncFilmCast;
use App\Models\Film;
use App\Services\PublicMediaStorage;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilmController extends Controller
{
    public function __construct(protected TmdbService $tmdb) {}

    public function index()
    {
        $films = Film::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(20);

        return view('admin.films.index', compact('films'));
    }

    public function create()
    {
        return view('admin.films.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'synopsis' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'release_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'cast' => 'nullable|string',
            'tmdb_id' => 'nullable|integer',
            'poster' => 'nullable|image|max:2048',
            'tmdb_poster_path' => ['nullable', 'string', 'max:255', 'regex:/^\/[A-Za-z0-9._-]+$/'],
        ]);

        $tmdbPosterPath = $validated['tmdb_poster_path'] ?? null;
        if ($request->hasFile('poster')) {
            $validated['poster_path'] = app(PublicMediaStorage::class)->store($request->file('poster'), 'posters');
        } elseif ($tmdbPosterPath) {
            $validated['poster_path'] = $tmdbPosterPath;
        }
        unset($validated['tmdb_poster_path']);

        $film = Film::create($validated);
        if ($film->tmdb_id || $tmdbPosterPath) {
            SyncFilmCast::dispatch($film->id, null, $tmdbPosterPath)->afterResponse();
        }

        return redirect()->route('admin.films.index')->with('status', 'Film added!');
    }

    public function bulkStore(Request $request)
    {
        $request->merge([
            'films' => json_decode((string) $request->input('films'), true),
        ]);

        $validated = $request->validate([
            'films' => 'required|array|min:1|max:20',
            'films.*.tmdb_id' => 'required|integer|min:1',
            'films.*.title' => 'required|string|max:255',
            'films.*.original_title' => 'nullable|string|max:255',
            'films.*.synopsis' => 'nullable|string',
            'films.*.genre' => 'nullable|string|max:100',
            'films.*.release_date' => 'nullable|date',
            'films.*.release_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'films.*.cast' => 'nullable|string',
            'films.*.poster_path' => ['nullable', 'string', 'max:255', 'regex:/^\/[A-Za-z0-9._-]+$/'],
        ]);

        $films = collect($validated['films'])->unique('tmdb_id')->values();
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($films, &$created, &$skipped): void {
            foreach ($films as $filmData) {
                if (Film::where('tmdb_id', $filmData['tmdb_id'])->exists()) {
                    $skipped++;
                    continue;
                }

                $posterPath = $filmData['poster_path'] ?? null;
                unset($filmData['poster_path']);
                $filmData['poster_path'] = $posterPath;
                $film = Film::create($filmData);
                SyncFilmCast::dispatch($film->id, null, $posterPath)->afterResponse();
                $created++;
            }
        });

        $message = $created.' film'.($created === 1 ? '' : 's').' added.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' already in the library and skipped.';
        }

        return redirect()->route('admin.films.index')->with('status', $message);
    }

    public function edit(Film $film)
    {
        return view('admin.films.edit', compact('film'));
    }

    public function update(Request $request, Film $film)
    {
        $previousPosterPath = $film->poster_path;
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'synopsis' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'release_year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'cast' => 'nullable|string',
            'tmdb_id' => 'nullable|integer',
            'poster' => 'nullable|image|max:2048',
            'tmdb_poster_path' => ['nullable', 'string', 'max:255', 'regex:/^\/[A-Za-z0-9._-]+$/'],
        ]);

        $tmdbPosterPath = $validated['tmdb_poster_path'] ?? null;
        if ($request->hasFile('poster')) {
            $validated['poster_path'] = app(PublicMediaStorage::class)->store($request->file('poster'), 'posters');
        } elseif ($tmdbPosterPath) {
            $validated['poster_path'] = $tmdbPosterPath;
        }
        unset($validated['tmdb_poster_path']);

        $film->update($validated);
        if (! $tmdbPosterPath && $previousPosterPath !== $film->poster_path) {
            app(PublicMediaStorage::class)->delete($previousPosterPath);
        }

        if ($film->tmdb_id || $tmdbPosterPath) {
            SyncFilmCast::dispatch(
                $film->id,
                null,
                $tmdbPosterPath,
                $tmdbPosterPath ? $previousPosterPath : null,
            )->afterResponse();
        }

        return redirect()->route('admin.films.index')->with('status', 'Film updated!');
    }

    public function destroy(Film $film)
    {
        $castMembers = $film->castMembers()->get();
        app(PublicMediaStorage::class)->delete($film->poster_path);
        $film->delete();

        foreach ($castMembers as $castMember) {
            app(PublicMediaStorage::class)->delete($castMember->pivot->profile_path);
            if (! $castMember->films()->exists()) {
                app(PublicMediaStorage::class)->delete($castMember->profile_path);
                $castMember->delete();
            }
        }

        return back()->with('status', 'Film deleted.');
    }

    public function tmdbSearch(Request $request)
    {
        $query = $request->validate([
            'q' => 'required|string|min:2',
        ])['q'];

        $movie = collect($this->tmdb->search($query))->first();

        if (! $movie) {
            return response()->json([
                'message' => 'No results found.',
            ], 404);
        }

        $details = $this->tmdb->details((int) $movie['id']);

        return response()->json([
            'tmdb_id' => $details['id'] ?? $movie['id'],
            'title' => $details['title'] ?? $movie['title'],
            'synopsis' => $details['overview'] ?? null,
            'genre' => collect($details['genres'] ?? [])->pluck('name')->join(', '),
            'release_date' => $details['release_date'] ?: null,
            'release_year' => ! empty($details['release_date']) ? substr($details['release_date'], 0, 4) : null,
            'cast' => collect($details['credits']['cast'] ?? [])->take(6)->pluck('name')->join(', '),
            'poster_path' => $details['poster_path'] ?? $movie['poster_path'] ?? null,
            'poster_url' => $this->tmdb->posterUrl($details['poster_path'] ?? $movie['poster_path'] ?? null),
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->validate([
            'q' => 'required|string|min:2',
        ])['q'];

        $results = collect($this->tmdb->search($query))->map(fn ($movie) => [
            'tmdb_id' => $movie['id'],
            'title' => $movie['title'],
            'release_year' => $movie['release_date'] ? substr($movie['release_date'], 0, 4) : null,
            'poster_url' => $this->tmdb->posterUrl($movie['poster_path'] ?? null),
        ]);

        return view('admin.films.search', compact('results', 'query'));
    }

    public function import(Request $request)
    {
        $tmdbId = $request->validate([
            'tmdb_id' => 'required|integer',
        ])['tmdb_id'];

        if (Film::where('tmdb_id', $tmdbId)->exists()) {
            return back()->with('status', 'That film is already imported.');
        }

        $details = $this->tmdb->details($tmdbId);
        $posterPath = $details['poster_path'] ?? null;

        $film = Film::create([
            'tmdb_id' => $details['id'],
            'title' => $details['title'],
            'original_title' => $details['original_title'] ?? null,
            'synopsis' => $details['overview'] ?? null,
            'genre' => collect($details['genres'] ?? [])->pluck('name')->join(', '),
            'release_year' => $details['release_date'] ? substr($details['release_date'], 0, 4) : null,
            'release_date' => $details['release_date'] ?: null,
            'cast' => collect($details['credits']['cast'] ?? [])->take(6)->pluck('name')->join(', '),
            'poster_path' => $posterPath,
        ]);
        SyncFilmCast::dispatch($film->id, $details['credits']['cast'] ?? [], $posterPath)->afterResponse();
        $film->syncTmdbKeywords(data_get($details, 'keywords.keywords', []));

        return redirect()->route('admin.films.index')->with('status', "Imported \"{$film->title}\"!");
    }
}
