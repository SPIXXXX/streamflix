<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            'tmdb_poster_url' => 'nullable|url',
        ]);

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
        } elseif (! empty($validated['tmdb_poster_url'])) {
            $contents = Http::get($validated['tmdb_poster_url'])->throw()->body();
            $filename = 'posters/'.($validated['tmdb_id'] ?? uniqid()).'-'.now()->timestamp.'.jpg';
            \Storage::disk('public')->put($filename, $contents);
            $validated['poster_path'] = $filename;
        }

        Film::create($validated);

        return redirect()->route('admin.films.index')->with('status', 'Film added!');
    }

    public function edit(Film $film)
    {
        return view('admin.films.edit', compact('film'));
    }

    public function update(Request $request, Film $film)
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
            'tmdb_poster_url' => 'nullable|url',
        ]);

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = $request->file('poster')->store('posters', 'public');
        } elseif (! empty($validated['tmdb_poster_url'])) {
            $contents = Http::get($validated['tmdb_poster_url'])->throw()->body();
            $filename = 'posters/'.($validated['tmdb_id'] ?? $film->id).'-'.now()->timestamp.'.jpg';
            \Storage::disk('public')->put($filename, $contents);
            $validated['poster_path'] = $filename;
        }

        $film->update($validated);

        return redirect()->route('admin.films.index')->with('status', 'Film updated!');
    }

    public function destroy(Film $film)
    {
        $film->delete();

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

        $film = Film::create([
            'tmdb_id' => $details['id'],
            'title' => $details['title'],
            'original_title' => $details['original_title'] ?? null,
            'synopsis' => $details['overview'] ?? null,
            'genre' => collect($details['genres'] ?? [])->pluck('name')->join(', '),
            'release_year' => $details['release_date'] ? substr($details['release_date'], 0, 4) : null,
            'release_date' => $details['release_date'] ?: null,
            'cast' => collect($details['credits']['cast'] ?? [])->take(6)->pluck('name')->join(', '),
        ]);
        $film->syncTmdbKeywords(data_get($details, 'keywords.keywords', []));

        if (! empty($details['poster_path'])) {
            $posterUrl = $this->tmdb->posterUrl($details['poster_path'], 'w500');
            $contents = Http::get($posterUrl)->body();
            $path = 'posters/'.$film->id.'.jpg';
            \Storage::disk('public')->put($path, $contents);
            $film->update(['poster_path' => $path]);
        }

        return redirect()->route('admin.films.index')->with('status', "Imported \"{$film->title}\"!");
    }
}
