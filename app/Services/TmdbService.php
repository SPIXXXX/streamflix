<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TmdbService
{
    protected string $baseUrl = 'https://api.themoviedb.org/3';

    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.tmdb.token'),
        ])
            ->baseUrl($this->baseUrl)
            ->withQueryParameters([
                'api_key' => config('services.tmdb.key'),
            ]);
    }

    public function search(string $query): array
    {
        $normalizedQuery = Str::lower(Str::squish($query));
        $cacheKey = 'tmdb.search.movie.'.sha1($normalizedQuery);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($normalizedQuery): array {
            $response = $this->client()->timeout(5)->get('/search/movie', ['query' => $normalizedQuery]);

            return $response->json('results') ?? [];
        });
    }

    public function details(int $tmdbId): array
    {
        $response = $this->client()->timeout(8)->get("/movie/{$tmdbId}", [
            'append_to_response' => 'credits,keywords',
        ]);

        return $response->json() ?? [];
    }

    /** Return cached genre and keyword metadata; unavailable data is cached briefly too. */
    public function movieMetadata(int $tmdbId): array
    {
        if (! config('services.tmdb.token') && ! config('services.tmdb.key')) {
            return ['available' => false];
        }

        $cacheKey = "tmdb.movie.{$tmdbId}.metadata";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client()->timeout(8)->get("/movie/{$tmdbId}", [
                'append_to_response' => 'keywords',
            ]);
        } catch (ConnectionException|RequestException) {
            $metadata = ['available' => false];
            Cache::put($cacheKey, $metadata, now()->addMinutes(10));

            return $metadata;
        }

        if (! $response->successful()) {
            $metadata = ['available' => false];
            Cache::put($cacheKey, $metadata, now()->addMinutes(10));

            return $metadata;
        }

        $details = $response->json() ?? [];
        $keywords = data_get($details, 'keywords.keywords', data_get($details, 'keywords.results', []));
        $metadata = [
            'available' => true,
            'genres' => collect($details['genres'] ?? [])->map(fn (array $genre): array => [
                'id' => (int) ($genre['id'] ?? 0),
                'name' => (string) ($genre['name'] ?? ''),
            ])->filter(fn (array $genre): bool => $genre['id'] > 0 && $genre['name'] !== '')->values()->all(),
            'keywords' => collect($keywords)->map(fn (array $keyword): array => [
                'id' => (int) ($keyword['id'] ?? 0),
                'name' => (string) ($keyword['name'] ?? ''),
            ])->filter(fn (array $keyword): bool => $keyword['id'] > 0 && $keyword['name'] !== '')->values()->all(),
        ];
        Cache::put($cacheKey, $metadata, now()->addDays(7));

        return $metadata;
    }

    public function castMembers(int $tmdbId, int $limit = 12): array
    {
        $cacheKey = "tmdb.movie.{$tmdbId}.cast.v2";
        $cachedCast = Cache::get($cacheKey);
        if (is_array($cachedCast)) {
            return $cachedCast;
        }

        try {
            $response = $this->client()->timeout(8)->get("/movie/{$tmdbId}", [
                'append_to_response' => 'credits',
            ]);
        } catch (ConnectionException) {
            return [];
        }

        if (! $response->successful() || ! is_array(data_get($response->json(), 'credits.cast'))) {
            return [];
        }

        $cast = collect($response->json('credits.cast'))
            ->sortBy('order')
            ->take($limit)
            ->map(fn (array $member): array => [
                'id' => (int) ($member['id'] ?? 0),
                'name' => $member['name'] ?? 'Unknown cast member',
                'character' => $member['character'] ?? null,
                'profile_url' => $this->posterUrl($member['profile_path'] ?? null, 'w185'),
            ])
            ->filter(fn (array $member): bool => $member['id'] > 0)
            ->values()
            ->all();

        Cache::put($cacheKey, $cast, now()->addDay());

        return $cast;
    }

    public function personDetails(int $personId): ?array
    {
        return Cache::remember("tmdb.person.{$personId}", now()->addDay(), function () use ($personId): ?array {
            try {
                $response = $this->client()->timeout(8)->get("/person/{$personId}");
                if (! $response->successful()) {
                    return null;
                }
                $person = $response->json();
            } catch (ConnectionException) {
                return null;
            }

            if (empty($person['id'])) {
                return null;
            }

            return [
                'id' => (int) $person['id'],
                'name' => $person['name'] ?? 'Unknown cast member',
                'biography' => $person['biography'] ?? null,
                'birthday' => $person['birthday'] ?? null,
                'deathday' => $person['deathday'] ?? null,
                'place_of_birth' => $person['place_of_birth'] ?? null,
                'known_for_department' => $person['known_for_department'] ?? null,
                'profile_url' => $this->posterUrl($person['profile_path'] ?? null, 'w500'),
            ];
        });
    }

    public function posterUrl(?string $path, string $size = 'w500'): ?string
    {
        return $path ? "https://image.tmdb.org/t/p/{$size}{$path}" : null;
    }

    /** @return array{contents: string, content_type: string}|null */
    public function downloadPoster(?string $path): ?array
    {
        if (! $path || ! preg_match('/^\/[A-Za-z0-9._-]+$/', $path)) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get($this->posterUrl($path, 'w500'));
        } catch (ConnectionException) {
            return null;
        }

        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
        if (! $response->successful() || ! str_starts_with($contentType, 'image/')) {
            return null;
        }

        $contents = $response->body();
        if ($contents === '' || strlen($contents) > 10 * 1024 * 1024) {
            return null;
        }

        return ['contents' => $contents, 'content_type' => $contentType];
    }
}
