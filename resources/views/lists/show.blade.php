<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('lists.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-sf-muted transition hover:text-white">
            <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7M8 12h13"/></svg>
            Movie Lists
        </a>

        <header class="mt-5 rounded-3xl border border-sf-border bg-gradient-to-br from-sf-surface via-sf-surface to-sf-bg p-6 shadow-xl sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($isFavoritesCollection)
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pink-500/10 text-pink-300">
                                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                            </span>
                        @else
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sf-blue/10 text-sf-text">
                                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5.5A1.5 1.5 0 0 1 5.5 4H20v16H5.5A1.5 1.5 0 0 0 4 21.5v-16Zm0 0A1.5 1.5 0 0 0 2.5 4H2v16h.5A1.5 1.5 0 0 1 4 21.5M8 9h8m-8 4h8"/></svg>
                            </span>
                        @endif
                        <span class="rounded-full border px-2.5 py-1 text-xs font-medium {{ $list->is_public ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-sf-border text-sf-muted' }}">
                            {{ $list->is_public ? 'Public list' : 'Private list' }}
                        </span>
                        @if ($list->is_official)
                            <span class="rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-xs font-medium text-amber-300">Official</span>
                        @endif
                    </div>
                    <h1 class="mt-4 break-words text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $list->title }}</h1>
                    @if ($list->description)
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-sf-muted sm:text-base">{{ $list->description }}</p>
                    @endif
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-sf-muted">
                        <span class="inline-flex items-center gap-2">
                            <x-user-avatar :user="$list->user" size="h-7 w-7" text-size="text-xs" />
                            <span>by <span class="font-medium text-sf-text">{{ $list->user->name }}</span></span>
                        </span>
                        <span>{{ $list->films_count }} {{ $list->films_count === 1 ? 'movie' : 'movies' }}</span>
                    </div>
                </div>

                @auth
                    @if (! $isFavoritesCollection && $list->user_id === auth()->id())
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('lists.edit', $list) }}" class="inline-flex items-center gap-2 rounded-lg border border-sf-border px-3.5 py-2 text-sm font-medium text-sf-text transition hover:border-sf-blue/50 hover:text-white">
                                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.3 6.3 3.4 3.4M4 20l4.5-1 11-11a2.4 2.4 0 0 0-3.4-3.4l-11 11L4 20Z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('lists.destroy', $list) }}" method="POST" data-confirm data-confirm-title="Delete this list?" data-confirm-message="{{ $list->title }} and its saved movie entries will be permanently removed." data-confirm-label="Delete list">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-red-500/20 px-3.5 py-2 text-sm font-medium text-red-300 transition hover:bg-red-500/10">
                                    <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 4v6m4-6v6M9 7V4h6v3m-9 0 1 13h10l1-13"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </header>

        @auth
            @if (! $isFavoritesCollection && $list->user_id === auth()->id())
                @if ($allFilms->isNotEmpty())
                    <form method="POST" action="{{ route('lists.films.add', $list) }}" class="mt-6 flex flex-col gap-3 rounded-2xl border border-sf-border bg-sf-surface p-4 sm:flex-row">
                        @csrf
                        <label for="add-film" class="sr-only">Choose a movie to add to this list</label>
                        <select id="add-film" name="film_id" required class="min-w-0 flex-1 rounded-lg border-sf-border bg-sf-bg text-sm text-white focus:border-sf-blue focus:ring-sf-blue">
                            <option value="">Add a movie to this list...</option>
                            @foreach ($allFilms as $film)
                                <option value="{{ $film->id }}">{{ $film->title }}{{ $film->release_year ? ' ('.$film->release_year.')' : '' }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sf-blue px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-4 focus:ring-sf-blue/30">
                            <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
                            Add Movie
                        </button>
                    </form>
                @endif
            @endif
        @endauth

        <section class="mt-8" aria-label="Movies in {{ $list->title }}">
            @if ($list->films->isNotEmpty())
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach ($list->films as $film)
                        @php
                            $isFavorite = in_array((int) $film->id, array_map('intval', $favoriteFilmIds), true);
                            $removeUrl = $isFavoritesCollection
                                ? route('films.favorite', $film)
                                : ($list->user_id === auth()->id() ? route('lists.films.remove', [$list, $film]) : null);
                        @endphp
                        <x-movie-poster-card
                            :film="$film"
                            :is-favorite="$isFavorite"
                            :remove-url="$removeUrl"
                            :remove-method="$isFavoritesCollection ? 'POST' : 'DELETE'"
                            :remove-label="$isFavoritesCollection ? 'Remove '.$film->title.' from favorites' : 'Remove '.$film->title.' from this list'"
                        />
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-sf-border bg-sf-surface/60 px-5 py-12 text-center">
                    <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-sf-blue/10 text-sf-text">
                        <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5.5A1.5 1.5 0 0 1 6.5 4H20v16H6.5A1.5 1.5 0 0 0 5 21.5v-16Zm0 0A1.5 1.5 0 0 0 3.5 4H3v16h.5A1.5 1.5 0 0 1 5 21.5"/></svg>
                    </span>
                    <h2 class="mt-4 text-lg font-semibold text-white">{{ $isFavoritesCollection ? 'No favorite movies yet.' : "This list doesn't have any movies yet." }}</h2>
                    <p class="mt-1 text-sm text-sf-muted">{{ $isFavoritesCollection ? 'Browse films and tap the heart to save them here.' : 'Movies added to this collection will show up here.' }}</p>
                    @if ($isFavoritesCollection)
                        <a href="{{ route('films.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg bg-sf-blue px-4 py-2 text-sm font-semibold text-white transition hover:bg-sf-blue-dark">Browse Films</a>
                    @elseif (auth()->check() && $list->user_id === auth()->id() && $allFilms->isEmpty())
                        <a href="{{ route('films.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-sf-border px-4 py-2 text-sm font-medium text-sf-text transition hover:border-sf-blue/50 hover:text-white">Browse Films</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
