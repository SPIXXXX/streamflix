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
            @if ($carouselFilms->isNotEmpty())
                <section class="mb-10" aria-labelledby="film-carousel-heading">
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sf-muted">Fresh picks, right now</p>
                            <h2 id="film-carousel-heading" class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">In the spotlight</h2>
                        </div>
                        <span class="hidden text-sm text-sf-muted sm:block">Recently added and popular this week</span>
                    </div>

                    <div data-film-carousel class="group relative isolate overflow-hidden rounded-3xl border border-sf-border bg-sf-bg shadow-2xl shadow-black/30" role="region" aria-roledescription="carousel" aria-label="Recently added and popular films">
                        <div data-carousel-slides>
                            @foreach ($carouselFilms as $index => $film)
                                @php
                                    $posterUrl = $film->posterUrl();
                                    $isPopularThisWeek = $popularThisWeek->contains('id', $film->id);
                                    $releaseYear = $film->release_year ?: $film->release_date?->format('Y');
                                @endphp
                                <article data-carousel-slide @if ($index !== 0) hidden @endif aria-hidden="{{ $index === 0 ? 'false' : 'true' }}" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $carouselFilms->count() }}" class="relative isolate min-h-[390px] sm:min-h-[430px] lg:min-h-[460px]">
                                    @if ($posterUrl)
                                        <img src="{{ $posterUrl }}" alt="" aria-hidden="true" class="absolute inset-0 -z-20 h-full w-full scale-110 object-cover object-center opacity-35 blur-xl" @if ($index > 0) loading="lazy" @else fetchpriority="high" @endif onerror="this.style.display='none'">
                                    @endif
                                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#0F1726] via-[#0F1726]/90 to-[#0F1726]/30"></div>
                                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#0F1726]/90 via-transparent to-[#0F1726]/20"></div>
                                    @if ($posterUrl)
                                        <img src="{{ $posterUrl }}" alt="{{ $film->title }} poster" class="absolute inset-y-0 right-0 z-0 h-full w-full object-contain object-right opacity-95 lg:w-1/2" @if ($index > 0) loading="lazy" @else fetchpriority="high" @endif onerror="this.style.display='none'">
                                    @endif

                                    <div class="relative z-10 flex min-h-[390px] flex-col justify-end p-6 pb-24 sm:min-h-[430px] sm:p-10 sm:pb-24 lg:min-h-[460px] lg:max-w-[48%] lg:p-12 lg:pb-24">
                                        <div class="mb-4 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-[#B8001F] px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-white">{{ $isPopularThisWeek ? 'Popular this week' : 'Recently added' }}</span>
                                            @if ($releaseYear)
                                                <span class="rounded-full border border-white/20 bg-black/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur">{{ $releaseYear }}</span>
                                            @endif
                                            @if ($film->genre)
                                                <span class="rounded-full border border-white/20 bg-black/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur">{{ \Illuminate\Support\Str::before($film->genre, ',') }}</span>
                                            @endif
                                        </div>
                                        <h3 class="max-w-2xl text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">{{ $film->title }}</h3>
                                        <p class="mt-4 line-clamp-3 max-w-xl text-sm leading-6 text-white/75 sm:text-base sm:leading-7">{{ $film->synopsis ?: 'Discover this film and see what the Cinevault community thinks.' }}</p>
                                        <div class="mt-6 flex flex-wrap items-center gap-4">
                                            <a href="{{ route('films.show', $film) }}" class="inline-flex min-h-12 items-center gap-2 rounded-full bg-[#B8001F] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-black/20 transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-white/80 focus:ring-offset-2 focus:ring-offset-[#0F1726]">
                                                Explore film
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                                            </a>
                                            @if (($film->reviews_count ?? 0) > 0)
                                                <span class="text-sm font-semibold text-white/80"><span class="text-amber-300">★</span> {{ number_format((float) ($film->reviews_avg_rating ?? 0), 1) }} member rating</span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if ($carouselFilms->count() > 1)
                            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-4 px-6 pb-6 sm:px-10 sm:pb-8 lg:px-12">
                                <div class="flex items-center gap-2" role="group" aria-label="Choose a featured film">
                                    @foreach ($carouselFilms as $index => $film)
                                        <button type="button" data-carousel-dot="{{ $index }}" aria-label="Show {{ $film->title }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" class="h-2 rounded-full bg-white/35 transition-all aria-[current=true]:w-8 aria-[current=true]:bg-white" @if ($index !== 0) tabindex="-1" @endif></button>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="mr-2 text-xs font-semibold tabular-nums text-white/65" aria-live="polite"><span data-carousel-counter>01</span><span class="px-1">/</span>{{ str_pad((string) $carouselFilms->count(), 2, '0', STR_PAD_LEFT) }}</span>
                                    <button type="button" data-carousel-prev aria-label="Previous featured film" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/80">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l4.22 4.22a.75.75 0 1 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 1 1 1.06 1.06L5.56 9.25h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <button type="button" data-carousel-next aria-label="Next featured film" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/80">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69l-4.22-4.22a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

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
