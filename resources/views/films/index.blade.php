<x-app-layout>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.24em] text-sf-blue">Discover something good</p>
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Films</h1>
                <p class="mt-2 max-w-xl text-sm text-sf-muted">Browse the community’s latest releases, favorites, and top-rated films.</p>
            </div>

            <form method="GET" action="{{ route('films.index') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <label class="sr-only" for="film-search">Search films</label>
                <input id="film-search" name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Search films..."
                    class="min-w-0 rounded-xl border border-sf-border bg-sf-surface px-4 py-2.5 text-sm text-white placeholder:text-sf-muted focus:border-sf-blue focus:ring-sf-blue sm:w-64">
                <label class="sr-only" for="film-genre">Filter by genre</label>
                <select id="film-genre" name="genre" class="rounded-xl border border-sf-border bg-sf-surface px-3 py-2.5 text-sm text-white focus:border-sf-blue focus:ring-sf-blue">
                    <option value="">All genres</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre }}" @selected(($filters['genre'] ?? '') === $genre)>{{ $genre }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-sf-blue px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-sf-blue/30">Search</button>
                @if (request()->filled('q') || request()->filled('genre'))
                    <a href="{{ route('films.index') }}" class="rounded-xl border border-sf-border px-4 py-2.5 text-center text-sm font-medium text-gray-300 transition hover:bg-white/5">Clear</a>
                @endif
            </form>
        </div>

        @if (isset($searchResults))
            <section aria-labelledby="film-search-results-title">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="film-search-results-title" class="text-xl font-semibold text-white">Search results</h2>
                        <p class="mt-1 text-sm text-sf-muted">{{ $searchResults->total() }} films found</p>
                    </div>
                </div>
                @if ($searchResults->isEmpty())
                    <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-10 text-center text-sm text-sf-muted">No films match those filters.</div>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @foreach ($searchResults as $film)
                            <x-movie-poster-card :film="$film" />
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $searchResults->links() }}</div>
                @endif
            </section>
        @else
            <section class="mb-10" aria-labelledby="popular-reviews-title">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="popular-reviews-title" class="text-xl font-semibold text-white sm:text-2xl">Popular Reviews This Week</h2>
                        <p class="mt-1 text-sm text-sf-muted">Reviews receiving the most agrees in the last 7 days.</p>
                    </div>
                    <a href="{{ route('films.collections', 'popular-reviews-this-week') }}" class="shrink-0 rounded-lg px-2 py-2 text-sm font-semibold text-sf-blue transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">View All <span aria-hidden="true">→</span></a>
                </div>
                @if ($popularReviews->isEmpty())
                    <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-8 text-sm text-sf-muted">No popular reviews this week yet.</div>
                @else
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($popularReviews as $review)
                            @include('films._popular-review-card', ['review' => $review])
                        @endforeach
                    </div>
                @endif
            </section>
            @include('films._shelf', [
                'id' => 'recently-added', 'title' => 'Recently Added', 'description' => 'Sorted by the release date provided by TMDB.',
                'films' => $recentlyAdded, 'emptyMessage' => 'No recently added movies are available.', 'viewAll' => 'recently-added',
            ])
            @include('films._shelf', [
                'id' => 'popular-this-week', 'title' => 'Popular This Week', 'description' => 'Based on activity from the last 7 days.',
                'films' => $popularThisWeek, 'emptyMessage' => 'Not enough recent activity to determine popular films yet.', 'viewAll' => 'popular-this-week',
            ])
            @include('films._shelf', [
                'id' => 'popular-films', 'title' => 'Popular Films', 'description' => 'Ranked by activity from members on this site.',
                'films' => $popularFilms, 'emptyMessage' => 'No popular movies are available yet.', 'viewAll' => 'popular-movies',
            ])
            @include('films._shelf', [
                'id' => 'highest-rated', 'title' => 'Highest Rated', 'description' => 'Member ratings, with at least two ratings per film.',
                'films' => $highestRated, 'emptyMessage' => 'Films need at least two member ratings to appear here.', 'viewAll' => 'highest-rated',
            ])
            @include('films._shelf', [
                'id' => 'explore-films', 'title' => 'Explore Films', 'description' => 'Browse the catalogue.',
                'films' => $exploreFilms, 'emptyMessage' => 'No films have been added yet.', 'viewAll' => 'all',
            ])
        @endif

        <p class="mt-10 text-center text-xs text-sf-muted">This product uses the TMDB API but is not endorsed or certified by TMDB.</p>
    </main>
</x-app-layout>
