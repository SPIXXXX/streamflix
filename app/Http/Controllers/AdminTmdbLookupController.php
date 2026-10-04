<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTmdbLookupController extends Controller
{
    public function __construct(private readonly TmdbService $tmdb) {}

    public function results(Request $request): JsonResponse
    {
        $query = $request->validate(['q' => 'required|string|min:2|max:100'])['q'];

        $results = collect($this->tmdb->search($query))
            ->take(8)
            ->map(fn (array $movie): array => [
                'tmdb_id' => $movie['id'],
                'title' => $movie['title'] ?? 'Untitled film',
                'original_title' => $movie['original_title'] ?? null,
                'synopsis' => $movie['overview'] ?? null,
                'release_date' => $movie['release_date'] ?? null,
                'release_year' => ! empty($movie['release_date']) ? substr($movie['release_date'], 0, 4) : null,
                'poster_path' => $movie['poster_path'] ?? null,
                'poster_url' => $this->tmdb->posterUrl($movie['poster_path'] ?? null, 'w185'),
            ])
            ->values();

        return response()->json(['results' => $results]);
    }

    public function details(int $tmdbId): JsonResponse
    {
        $details = $this->tmdb->details($tmdbId);

        if (empty($details['id'])) {
            return response()->json(['message' => 'TMDB could not find that film.'], 404);
        }

        return response()->json([
            'tmdb_id' => $details['id'],
            'title' => $details['title'] ?? '',
            'original_title' => $details['original_title'] ?? null,
            'synopsis' => $details['overview'] ?? null,
            'genre' => collect($details['genres'] ?? [])->pluck('name')->join(', '),
            'release_date' => $details['release_date'] ?? null,
            'release_year' => ! empty($details['release_date']) ? substr($details['release_date'], 0, 4) : null,
            'cast' => collect($details['credits']['cast'] ?? [])->take(6)->pluck('name')->join(', '),
            'poster_path' => $details['poster_path'] ?? null,
            'poster_url' => $this->tmdb->posterUrl($details['poster_path'] ?? null),
        ]);
    }
}
