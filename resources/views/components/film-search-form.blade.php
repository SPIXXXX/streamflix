@php($selectedGenres = $filters['genres'] ?? [])
<form method="GET" action="{{ $action }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-stretch sm:gap-0">
    <div x-data="{ open: false, selectedCount: @js(count($selectedGenres)) }" class="relative shrink-0">
        <button type="button" @click="open = !open" @click.outside="open = false" :aria-expanded="open.toString()" aria-haspopup="listbox" aria-controls="film-category-options"
            class="inline-flex w-full items-center justify-between gap-2 rounded-lg border border-sf-border bg-sf-surface px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 sm:min-w-44 sm:rounded-r-none focus:ring-sf-blue/50">
            <span class="inline-flex min-w-0 items-center gap-2">
                <svg class="h-4 w-4 shrink-0 text-sf-muted" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h5v5H3V3Zm9 0h5v5h-5V3ZM3 12h5v5H3v-5Zm9 0h5v5h-5v-5Z"/></svg>
                <span x-text="selectedCount ? `${selectedCount} categor${selectedCount === 1 ? 'y' : 'ies'}` : 'All categories'">All categories</span>
            </span>
            <svg class="h-4 w-4 shrink-0 text-sf-muted transition" :class="open ? 'rotate-180' : ''" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
        </button>
        <div id="film-category-options" x-cloak x-show="open" x-transition.origin.top.left @click.outside="open = false" class="absolute left-0 top-full z-30 mt-2 max-h-72 w-full min-w-56 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden rounded-xl border border-sf-border bg-sf-surface p-2 shadow-xl sm:w-64" role="group" aria-label="Filter by categories">
            @forelse ($genres as $genre)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-200 transition hover:bg-white/5">
                    <input type="checkbox" name="genres[]" value="{{ $genre }}" @checked(in_array($genre, $selectedGenres, true)) @change="selectedCount = $el.form.querySelectorAll('input[type=checkbox]:checked').length"
                        class="h-4 w-4 rounded border-sf-border bg-sf-bg text-sf-blue focus:ring-2 focus:ring-sf-blue">
                    <span>{{ $genre }}</span>
                </label>
            @empty
                <p class="px-3 py-2 text-sm text-sf-muted">No categories available yet.</p>
            @endforelse
        </div>
    </div>
    <label class="sr-only" for="film-search-query">Search films</label>
    <input id="film-search-query" name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Search films..."
        class="min-w-0 flex-1 rounded-lg border border-sf-border bg-sf-surface px-4 py-2.5 text-sm text-white placeholder:text-sf-muted focus:border-sf-blue focus:outline-none focus:ring-2 focus:ring-sf-blue/50 sm:rounded-none">
    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-sf-blue px-4 py-2.5 text-sm font-semibold text-white transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-sf-blue/30 sm:rounded-l-none">
        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 20 20" fill="none"><circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.8"/><path d="m13 13 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        Search
    </button>
    @if (($filters['q'] ?? '') !== '' || $selectedGenres !== [])
        <a href="{{ $clearUrl }}" class="inline-flex items-center justify-center rounded-lg border border-sf-border px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/5 sm:ml-2">Clear</a>
    @endif
</form>
