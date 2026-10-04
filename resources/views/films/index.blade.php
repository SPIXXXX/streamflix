<x-app-layout>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Films</h1>
            </div>

            <div class="w-full sm:max-w-2xl">
                @include('components.film-search-form', ['action' => route('films.index'), 'clearUrl' => route('films.index'), 'filters' => $filters, 'genres' => $genres])
            </div>
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
                    </div>
                    <a href="{{ route('films.collections', 'popular-reviews-this-week') }}" class="shrink-0 rounded-lg px-2 py-2 text-sm font-semibold text-sf-text transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">View All <span aria-hidden="true">→</span></a>
                </div>
                @if ($popularReviews->isEmpty())
                    <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-8 text-sm text-sf-muted">No popular reviews this week yet.</div>
                @else
                    <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden sm:mx-0 sm:px-0" role="region" aria-label="Popular reviews this week" tabindex="0">
                        @foreach ($popularReviews as $review)
                            <div class="w-[85vw] max-w-sm shrink-0 snap-start">
                                @include('films._popular-review-card', ['review' => $review])
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
            @include('films._shelf', [
                'id' => 'recently-added', 'title' => 'Recently Added',
                'films' => $recentlyAdded, 'emptyMessage' => 'No recently added movies are available.', 'viewAll' => 'recently-added',
            ])
            @include('films._shelf', [
                'id' => 'popular-this-week', 'title' => 'Popular This Week',
                'films' => $popularThisWeek, 'emptyMessage' => 'Not enough recent activity to determine popular films yet.', 'viewAll' => 'popular-this-week',
            ])
            @include('films._shelf', [
                'id' => 'popular-films', 'title' => 'Popular Films',
                'films' => $popularFilms, 'emptyMessage' => 'No popular movies are available yet.', 'viewAll' => 'popular-movies',
            ])
            @include('films._shelf', [
                'id' => 'highest-rated', 'title' => 'Highest Rated',
                'films' => $highestRated, 'emptyMessage' => 'Films need at least two member ratings to appear here.', 'viewAll' => 'highest-rated',
            ])
            @include('films._shelf', [
                'id' => 'explore-films', 'title' => 'Explore Films',
                'films' => $exploreFilms, 'emptyMessage' => 'No films have been added yet.', 'viewAll' => 'all',
            ])
        @endif

        <p class="mt-10 text-center text-xs text-sf-muted">This product uses the TMDB API but is not endorsed or certified by TMDB.</p>
    </main>
</x-app-layout>
