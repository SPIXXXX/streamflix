    <div class="max-w-5xl mx-auto py-8 px-4">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="w-full md:w-64 shrink-0">
                <x-film-poster-image :film="$film" container-class="aspect-[2/3] w-full overflow-hidden rounded-lg shadow-lg" :alt="$film->title.' poster'" loading="eager" />
            </div>

            <div class="flex-1">
                <h1 class="text-3xl font-bold text-white">{{ $film->title }}</h1>
                <p class="text-gray-400 mt-1">{{ $film->genre }} • {{ $film->release_date?->format('M j, Y') ?: $film->release_year }}</p>
                <p class="text-gray-300 mt-4">{{ $film->synopsis }}</p>

                <p class="text-blue-400 mt-4 font-semibold">
                    ⭐ {{ number_format((float) ($film->reviews_avg_rating ?? 0), 1) }} ({{ $film->reviews->count() }} reviews)
                </p>

                @auth
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('films.favorite', $film) }}" data-favorite-form>
                            @csrf
                            <button type="submit" data-favorite-button aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
                                    class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $isFavorite ? 'border-pink-500/40 bg-pink-500/10 text-pink-300 hover:bg-pink-500/20' : 'border-sf-border bg-sf-surface text-gray-200 hover:border-pink-500/50 hover:text-pink-300' }}">
                                <svg data-favorite-icon class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/>
                                </svg>
                                <span data-favorite-label>{{ $isFavorite ? 'Favorited' : 'Favorite' }}</span>
                            </button>
                        </form>

                        <details class="group relative">
                            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-sf-border bg-sf-surface px-4 py-2 text-sm font-semibold text-gray-200 transition hover:border-sf-blue/50 hover:text-white [&::-webkit-details-marker]:hidden">
                                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5.5A1.5 1.5 0 0 1 6.5 4H20v16H6.5A1.5 1.5 0 0 0 5 21.5m0-16v16m0-16A1.5 1.5 0 0 0 3.5 4H3v16h.5A1.5 1.5 0 0 1 5 21.5m4-12h7m-7 4h7"/>
                                </svg>
                                Add to list
                                <svg class="h-4 w-4 transition group-open:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </summary>
                            <div class="absolute left-0 top-full z-30 mt-2 w-64 rounded-xl border border-sf-border bg-sf-surface p-2 shadow-2xl">
                                @forelse (($userLists ?? collect()) as $movieList)
                                    <form method="POST" action="{{ route('lists.films.add', $movieList) }}">
                                        @csrf
                                        <input type="hidden" name="film_id" value="{{ $film->id }}">
                                        <button type="submit" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-gray-200 transition hover:bg-sf-surface-light hover:text-white">
                                            <span class="truncate">{{ $movieList->title }}</span>
                                            @if ($movieList->contains_film)
                                                <span class="ml-2 shrink-0 text-xs text-sf-blue">Added</span>
                                            @endif
                                        </button>
                                    </form>
                                @empty
                                    <p class="px-3 py-2 text-sm text-sf-muted">You haven’t created a list yet.</p>
                                @endforelse
                                <a href="{{ route('lists.create') }}" class="mt-1 block rounded-lg border-t border-sf-border px-3 py-2 text-sm font-medium text-sf-blue hover:bg-sf-surface-light">Create a movie list</a>
                            </div>
                        </details>
                    </div>
                @endauth
            </div>
        </div>

        <section class="mt-10" aria-labelledby="cast-heading">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <h2 id="cast-heading" class="text-xl font-bold text-white">Cast</h2>
                    <p class="mt-1 text-sm text-gray-400">Select a cast member to see their profile.</p>
                </div>
                <span class="text-xs text-gray-500">Cast data by TMDB</span>
            </div>

            @if (count($cast))
                <div class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" aria-label="Cast members">
                    @foreach ($cast as $castMember)
                        <button type="button"
                                data-cast-member
                                data-cast-id="{{ $castMember['id'] }}"
                                data-cast-name="{{ $castMember['name'] }}"
                                data-cast-character="{{ $castMember['character'] ?? '' }}"
                                data-cast-photo="{{ $castMember['profile_url'] ?? '' }}"
                                data-cast-url="{{ route('films.cast-member', ['film' => $film, 'personId' => $castMember['id']]) }}"
                                data-modal-target="cast-member-modal"
                                data-modal-toggle="cast-member-modal"
                                aria-label="View {{ $castMember['name'] }}{{ !empty($castMember['character']) ? ', playing '.$castMember['character'] : '' }}"
                                class="group flex w-24 shrink-0 snap-start flex-col items-center rounded-xl p-1 text-center transition hover:bg-sf-surface focus:outline-none focus-visible:ring-2 focus-visible:ring-sf-blue">
                            @if ($castMember['profile_url'])
                                <img src="{{ $castMember['profile_url'] }}" alt="{{ $castMember['name'] }}" loading="lazy" class="h-20 w-20 rounded-full border-2 border-sf-border object-cover shadow-lg transition group-hover:border-sf-blue group-hover:scale-105">
                            @else
                                <span aria-hidden="true" class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-sf-border bg-sf-surface-light text-xl font-semibold text-sf-muted transition group-hover:border-sf-blue group-hover:text-white">{{ strtoupper(substr($castMember['name'], 0, 1)) }}</span>
                            @endif
                            <span class="mt-2 line-clamp-2 text-sm font-medium text-white group-hover:text-sf-blue">{{ $castMember['name'] }}</span>
                            @if ($castMember['character'])
                                <span class="mt-0.5 line-clamp-1 text-xs text-sf-muted">{{ $castMember['character'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif ($film->cast)
                <p class="rounded-xl border border-sf-border bg-sf-surface p-4 text-sm text-gray-300">{{ $film->cast }}</p>
            @else
                <p class="rounded-xl border border-sf-border bg-sf-surface p-4 text-sm text-gray-400">Cast profiles are not available for this film yet.</p>
            @endif
        </section>

        <div id="cast-member-modal" tabindex="-1" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="cast-member-name" class="hidden fixed inset-0 z-50 h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-black/70 p-4">
            <div class="relative mx-auto my-8 w-full max-w-2xl">
                <div class="relative overflow-hidden rounded-2xl border border-sf-border bg-sf-surface shadow-2xl shadow-black/50">
                    <button type="button" data-modal-hide="cast-member-modal" aria-label="Close cast profile" class="absolute end-3 top-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-lg text-sf-muted hover:bg-sf-surface-light hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue">
                        <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 12 12M13 1 1 13"/></svg>
                    </button>
                    <div class="grid gap-5 p-5 sm:grid-cols-[10rem_1fr] sm:p-7">
                        <div class="mx-auto sm:mx-0">
                            <img id="cast-member-photo" alt="" class="hidden h-40 w-40 rounded-xl object-cover shadow-lg sm:h-36 sm:w-36">
                            <div id="cast-member-photo-placeholder" class="flex h-40 w-40 items-center justify-center rounded-xl bg-sf-surface-light text-4xl font-bold text-sf-blue sm:h-36 sm:w-36">?</div>
                        </div>
                        <div class="min-w-0">
                            <h3 id="cast-member-name" class="pr-8 text-2xl font-bold text-white">Cast member</h3>
                            <p id="cast-member-character" class="mt-1 text-sm text-sf-blue"></p>
                            <p id="cast-member-department" class="mt-2 text-xs font-semibold uppercase tracking-wide text-sf-muted"></p>
                            <ul id="cast-member-facts" class="mt-3 space-y-1 text-sm text-gray-300"></ul>
                            <p id="cast-member-biography" class="mt-4 max-h-56 overflow-y-auto whitespace-pre-line text-sm leading-6 text-gray-300 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">Loading cast details…</p>
                        </div>
                    </div>
                    <div class="border-t border-sf-border px-5 py-3 text-xs text-sf-muted">Cast information and portraits provided by TMDB.</div>
                </div>
            </div>
        </div>

        <section class="mt-10 w-full" aria-labelledby="reviews">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="reviews" class="text-xl font-bold text-white sm:text-2xl">Reviews <span class="text-sm font-medium text-sf-muted">({{ $film->reviews->count() }})</span></h2>
                    <p class="mt-1 text-sm text-sf-muted">Rate the film and share what you thought.</p>
                </div>
            </div>

            @auth
                <form method="POST" action="{{ route('films.reviews.store', $film) }}"
                      data-loading-form
                      x-data="{ rating: @js((int) old('rating', 0)), hover: 0, comment: @js(old('comment', '')) }"
                      class="mb-6 rounded-xl border border-sf-border bg-sf-surface p-5 shadow-sm sm:p-6">
                    @csrf
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <label id="review-rating-label" class="block text-sm font-semibold text-white">Your rating <span class="text-red-400">*</span></label>
                        <span class="text-xs text-sf-muted" x-text="rating ? `${rating} out of 5 stars` : 'Choose 1 to 5 stars'"></span>
                    </div>
                    <div class="mb-5 flex items-center gap-1" role="radiogroup" aria-labelledby="review-rating-label">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="rating = i" @mouseenter="hover = i" @mouseleave="hover = 0"
                                    @keydown.left.prevent="rating = Math.max(1, rating - 1)" @keydown.right.prevent="rating = Math.min(5, rating + 1)"
                                    :aria-checked="rating === i" :aria-label="`${i} star${i === 1 ? '' : 's'}`" role="radio" :tabindex="rating === i || (!rating && i === 1) ? 0 : -1"
                                    class="rounded-md p-1 text-3xl transition duration-150 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-sf-blue/50"
                                    :class="(hover || rating) >= i ? 'text-amber-300' : 'text-sf-border'">
                                <span aria-hidden="true">★</span>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="rating" x-model="rating">

                    <div class="mb-4 rounded-lg border border-sf-border bg-sf-bg px-4 py-3 focus-within:border-sf-blue focus-within:ring-2 focus-within:ring-sf-blue/20">
                        <label for="film-review-comment" class="mb-2 block text-sm font-semibold text-white">Your review <span class="text-red-400">*</span></label>
                        <textarea id="film-review-comment" name="comment" rows="4" maxlength="2000" required x-model="comment" placeholder="What stood out to you about this film?"
                                  class="w-full resize-y border-0 bg-transparent p-0 text-sm leading-6 text-white placeholder:text-sf-muted focus:ring-0">{{ old('comment') }}</textarea>
                        <div class="mt-2 flex justify-between gap-3 border-t border-sf-border pt-2 text-xs text-sf-muted">
                            <span>Your comment is required. Keep it under 2,000 characters.</span>
                            <span class="shrink-0" x-text="`${comment.length}/2000`">0/2000</span>
                        </div>
                    </div>
                    @error('rating')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror
                    @error('comment')<p class="mb-3 text-sm text-red-400">{{ $message }}</p>@enderror

                    <button type="submit"
                            :class="rating === 0 || !comment.trim() ? 'cursor-not-allowed opacity-50' : 'hover:bg-sf-blue-dark'"
                            :disabled="rating === 0 || !comment.trim()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-sf-blue px-5 py-2.5 text-sm font-semibold text-white shadow-glow-blue transition focus:outline-none focus:ring-4 focus:ring-sf-blue/30 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M2.4 2.2a1 1 0 0 1 1.05-.15l14 6.5a1.6 1.6 0 0 1 0 2.9l-14 6.5A1 1 0 0 1 2.05 17l1.4-5.6 7.3-1.4-7.3-1.4-1.4-5.6a1 1 0 0 1 .35-.8Z"/></svg>
                        Post review
                    </button>
                </form>
            @else
                <div class="mb-6 rounded-xl border border-sf-border bg-sf-surface p-5 text-sm text-sf-muted">
                    <a href="{{ route('login') }}" class="font-semibold text-sf-blue hover:underline">Log in</a> to rate and review this film.
                </div>
            @endauth

            @forelse ($film->reviews as $review)
                <x-review-card :review="$review" :show-date="true" />
            @empty
                <p class="rounded-xl border border-dashed border-sf-border px-5 py-8 text-center text-sm text-sf-muted">No reviews yet. Be the first to share your thoughts.</p>
            @endforelse
        </section>
    </div>
