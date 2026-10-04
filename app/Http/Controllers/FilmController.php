<?php

namespace App\Http\Controllers;

use App\Jobs\SyncFilmCast;
use App\Models\CastMember;
use App\Models\Film;
use App\Models\Review;
use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FilmController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'genre' => 'nullable|string|max:100',
        ]);
        $weekAgo = now()->subDays(7);
        $buildFilmQuery = function () use ($filters): Builder {
            return Film::query()
                ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('synopsis', 'like', "%{$search}%");
                    });
                })
                ->when($filters['genre'] ?? null, fn (Builder $query, string $genre) => $query->where('genre', 'like', "%{$genre}%"));
        };
        $genres = Film::query()->whereNotNull('genre')->distinct()->pluck('genre')
            ->flatMap(fn (string $genre): array => explode(',', $genre))
            ->map(fn (string $genre): string => trim($genre))
            ->filter()->unique()->sort()->values();
        if ($request->filled('q') || $request->filled('genre')) {
            $searchResults = $buildFilmQuery()->withCount('reviews')->withAvg('reviews', 'rating')
                ->orderBy('title')->paginate(24)->withQueryString();

            return view('films.index', compact('genres', 'searchResults', 'filters'));
        }
        $recentlyAdded = $buildFilmQuery()->withCount('reviews')->withAvg('reviews', 'rating')
            ->whereNotNull('release_date')->orderByDesc('release_date')->limit(12)->get();
        $popularThisWeek = $buildFilmQuery()->withCount('reviews')->withAvg('reviews', 'rating')->withCount([
            'reviews as recent_reviews_count' => fn (Builder $query) => $query->where('created_at', '>=', $weekAgo),
            'favoritedBy as recent_favorites_count' => fn (Builder $query) => $query->where('film_favorites.created_at', '>=', $weekAgo),
            'movieLists as recent_list_additions_count' => fn (Builder $query) => $query->where('movie_lists.is_official', false)->where('list_films.created_at', '>=', $weekAgo),
        ])->where(function (Builder $query) use ($weekAgo): void {
            $query->whereHas('reviews', fn (Builder $reviews) => $reviews->where('created_at', '>=', $weekAgo))
                ->orWhereHas('favoritedBy', fn (Builder $favorites) => $favorites->where('film_favorites.created_at', '>=', $weekAgo))
                ->orWhereHas('movieLists', fn (Builder $lists) => $lists->where('movie_lists.is_official', false)->where('list_films.created_at', '>=', $weekAgo));
        })->orderByRaw('recent_reviews_count + recent_favorites_count + recent_list_additions_count DESC')->orderByDesc('title')->limit(12)->get();
        $popularFilms = $buildFilmQuery()->withCount('reviews')->withAvg('reviews', 'rating')->withCount([
            'reviews as activity_reviews_count',
            'favoritedBy as activity_favorites_count',
            'movieLists as activity_list_additions_count' => fn (Builder $query) => $query->where('movie_lists.is_official', false),
        ])->where(function (Builder $query): void {
            $query->whereHas('reviews')->orWhereHas('favoritedBy')
                ->orWhereHas('movieLists', fn (Builder $lists) => $lists->where('movie_lists.is_official', false));
        })->orderByRaw('activity_reviews_count + activity_favorites_count + activity_list_additions_count DESC')->orderByDesc('title')->limit(12)->get();
        $highestRated = $buildFilmQuery()->has('reviews', '>=', 2)->withCount('reviews')->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count')->limit(12)->get();
        $popularReviews = Review::with(['user', 'film'])
            ->withCount(['reactions as weekly_agree_count' => fn (Builder $query) => $query->where('reaction', 'agree')->where('created_at', '>=', $weekAgo)])
            ->where('created_at', '>=', $weekAgo)->orderByDesc('weekly_agree_count')->orderByDesc('created_at')->orderByDesc('id')->take(5)->get();
        $exploreFilms = $buildFilmQuery()->withCount('reviews')->withAvg('reviews', 'rating')
            ->orderByDesc('release_date')->orderByDesc('created_at')->limit(24)->get();

        return view('films.index', compact('genres', 'recentlyAdded', 'popularThisWeek', 'popularFilms', 'highestRated', 'popularReviews', 'exploreFilms', 'filters'));
    }

    public function collection(string $category): View
    {
        $weekAgo = now()->subDays(7);
        $titles = [
            'popular-reviews-this-week' => 'Popular Reviews This Week',
            'recently-added' => 'Recently Added',
            'popular-this-week' => 'Popular This Week',
            'popular-movies' => 'Popular Movies',
            'highest-rated' => 'Highest Rated',
            'all' => 'Explore Films',
        ];
        abort_unless(isset($titles[$category]), 404);
        $title = $titles[$category];

        if ($category === 'popular-reviews-this-week') {
            $popularReviews = Review::query()
                ->with(['user', 'film'])
                ->withCount(['reactions as weekly_agree_count' => fn (Builder $query) => $query
                    ->where('reaction', 'agree')
                    ->where('created_at', '>=', $weekAgo)])
                ->where('created_at', '>=', $weekAgo)
                ->orderByDesc('weekly_agree_count')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(24);

            return view('films.collection', compact('category', 'popularReviews', 'title'));
        }

        $baseQuery = Film::query()->withCount('reviews')->withAvg('reviews', 'rating');
        $films = match ($category) {
            'recently-added' => $baseQuery->whereNotNull('release_date')->orderByDesc('release_date'),
            'popular-this-week' => $baseQuery
                ->withCount([
                    'reviews as recent_reviews_count' => fn (Builder $query) => $query->where('created_at', '>=', $weekAgo),
                    'favoritedBy as recent_favorites_count' => fn (Builder $query) => $query->where('film_favorites.created_at', '>=', $weekAgo),
                    'movieLists as recent_list_additions_count' => fn (Builder $query) => $query
                        ->where('movie_lists.is_official', false)
                        ->where('list_films.created_at', '>=', $weekAgo),
                ])
                ->where(function (Builder $query) use ($weekAgo): void {
                    $query->whereHas('reviews', fn (Builder $reviews) => $reviews->where('created_at', '>=', $weekAgo))
                        ->orWhereHas('favoritedBy', fn (Builder $favorites) => $favorites->where('film_favorites.created_at', '>=', $weekAgo))
                        ->orWhereHas('movieLists', fn (Builder $lists) => $lists
                            ->where('movie_lists.is_official', false)
                            ->where('list_films.created_at', '>=', $weekAgo));
                })
                ->orderByRaw('recent_reviews_count + recent_favorites_count + recent_list_additions_count DESC')
                ->orderByDesc('title'),
            'popular-movies' => $baseQuery
                ->withCount([
                    'reviews as activity_reviews_count',
                    'favoritedBy as activity_favorites_count',
                    'movieLists as activity_list_additions_count' => fn (Builder $query) => $query->where('movie_lists.is_official', false),
                ])
                ->where(function (Builder $query): void {
                    $query->whereHas('reviews')
                        ->orWhereHas('favoritedBy')
                        ->orWhereHas('movieLists', fn (Builder $lists) => $lists->where('movie_lists.is_official', false));
                })
                ->orderByRaw('activity_reviews_count + activity_favorites_count + activity_list_additions_count DESC')
                ->orderByDesc('title'),
            'highest-rated' => $baseQuery->has('reviews', '>=', 2)
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('reviews_count'),
            'all' => $baseQuery->orderByDesc('release_date')->orderByDesc('created_at'),
        };
        $films = $films->paginate(24);

        return view('films.collection', compact('category', 'films', 'title'));
    }

    public function show(Film $film): View
    {
        if ($film->tmdb_id && ! $film->castMembers()->exists()) {
            SyncFilmCast::dispatch($film->id)->afterResponse();
        }

        $film->load([
            'reviews' => fn ($query) => $query
                ->with('user')
                ->withCount([
                    'reactions as agree_count' => fn (Builder $reactions) => $reactions->where('reaction', 'agree'),
                    'reactions as disagree_count' => fn (Builder $reactions) => $reactions->where('reaction', 'disagree'),
                ]),
            'teasers',
            'castMembers',
        ])->loadAvg('reviews', 'rating');
        $cast = $film->castMembers->map(fn (CastMember $member): array => [
            'id' => $member->tmdb_id,
            'name' => $member->name,
            'character' => $member->pivot->character,
            'profile_url' => $member->pivot->profile_path ?: $member->profileUrl(),
        ])->all();
        $userLists = auth()->user()?->movieLists()
            ->where('is_official', false)
            ->withCount('films')
            ->withExists(['films as contains_film' => fn (Builder $query) => $query->whereKey($film->id)])
            ->get() ?? collect();
        $isFavorite = auth()->user()?->favoriteFilms()->whereKey($film->id)->exists() ?? false;

        return view('films.show', compact('film', 'cast', 'userLists', 'isFavorite'));
    }

    public function toggleFavorite(Request $request, Film $film): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if ($user->favoriteFilms()->whereKey($film->id)->exists()) {
            $user->favoriteFilms()->detach($film->id);
            $message = 'Removed from your favorites.';
        } else {
            $user->favoriteFilms()->syncWithoutDetaching([$film->id]);
            $message = 'Added to your favorites.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'is_favorite' => $user->favoriteFilms()->whereKey($film->id)->exists(),
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    public function castMember(Film $film, int $personId, TmdbService $tmdb): JsonResponse
    {
        $filmCastMember = $film->castMembers()->where('tmdb_id', $personId)->first();
        $castMember = CastMember::where('tmdb_id', $personId)->first();
        $filmProfileUrl = $filmCastMember?->pivot->profile_path;
        if ($castMember?->biography_fetched_at) {
            return response()->json($this->castMemberPayload($castMember, $filmProfileUrl));
        }

        $person = $tmdb->personDetails($personId);

        if (! $person) {
            if (! $castMember) {
                return response()->json(['message' => 'Cast details are not available.'], 404);
            }

            return response()->json($this->castMemberPayload($castMember, $filmProfileUrl));
        }

        $castMember = CastMember::updateOrCreate(
            ['tmdb_id' => $personId],
            [
                'name' => $person['name'],
                'biography' => $person['biography'],
                'birthday' => $person['birthday'],
                'deathday' => $person['deathday'],
                'place_of_birth' => $person['place_of_birth'],
                'known_for_department' => $person['known_for_department'],
                'biography_fetched_at' => now(),
            ],
        );

        return response()->json($this->castMemberPayload($castMember, $filmProfileUrl));
    }

    private function castMemberPayload(CastMember $castMember, ?string $profileUrl = null): array
    {
        return [
            'id' => $castMember->tmdb_id,
            'name' => $castMember->name,
            'biography' => $castMember->biography,
            'birthday' => $castMember->birthday?->toDateString(),
            'deathday' => $castMember->deathday?->toDateString(),
            'place_of_birth' => $castMember->place_of_birth,
            'known_for_department' => $castMember->known_for_department,
            'profile_url' => $profileUrl ?: $castMember->profileUrl(),
        ];
    }

    public function storeReview(Request $request, Film $film)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $film->reviews()->create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        return back()->with('status', 'Review posted!');
    }

    public function updateReview(Request $request, Review $review)
    {
        abort_unless($review->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update($validated);

        return back()->with('status', 'Review updated.');
    }

    public function destroyReview(Review $review)
    {
        abort_unless($review->user_id === Auth::id(), 403);

        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
