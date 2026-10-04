@php($selectedGenres = $filters['genres'] ?? [])
<form method="GET" action="{{ $action }}" data-loading-form class="flex w-full items-stretch gap-2">
    <div class="relative min-w-0 flex-1">
        <div data-film-category-control x-data="{ open: false, selectedCount: @js(count($selectedGenres)) }" class="absolute inset-y-0 left-0 z-20">
            <button type="button" @click="open = !open" @click.outside="open = false" :aria-expanded="open.toString()" aria-haspopup="listbox" aria-controls="film-category-options" aria-label="Filter by movie category"
                class="relative inline-flex h-full items-center justify-center rounded-l-xl px-3 text-sf-muted transition hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/50">
                <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h5v5H3V3Zm9 0h5v5h-5V3ZM3 12h5v5H3v-5Zm9 0h5v5h-5v-5Z"/></svg>
                <span x-cloak x-show="selectedCount" class="absolute right-1 top-1 h-2 w-2 rounded-full bg-sf-blue" aria-hidden="true"></span>
            </button>
            <div id="film-category-options" x-cloak x-show="open" x-transition.origin.top.left @click.outside="open = false" class="absolute left-0 top-full mt-2 max-h-72 w-64 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden rounded-xl border border-sf-border bg-sf-surface p-2 shadow-xl" role="group" aria-label="Filter by categories">
                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-sf-muted">Movie categories</p>
                @forelse ($genres as $genre)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-200 transition hover:bg-white/5">
                        <input type="checkbox" name="genres[]" value="{{ $genre }}" @checked(in_array($genre, $selectedGenres, true)) @change="selectedCount = $el.form.querySelectorAll('input[type=checkbox]:checked').length" class="h-4 w-4 rounded border-sf-border bg-sf-bg text-sf-text focus:ring-2 focus:ring-sf-blue">
                        <span>{{ $genre }}</span>
                    </label>
                @empty
                    <p class="px-3 py-2 text-sm text-sf-muted">No categories available yet.</p>
                @endforelse
            </div>
        </div>
        <label class="sr-only" for="film-search-query">Search films</label>
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-11">
            <svg class="h-4 w-4 text-sf-muted" aria-hidden="true" viewBox="0 0 20 20" fill="none"><circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.8"/><path d="m13 13 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <input id="film-search-query" name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Search films..."
            class="block w-full rounded-xl border border-sf-border bg-sf-surface py-3 pl-[4.25rem] pr-24 text-sm text-white shadow-sm placeholder:text-sf-muted focus:border-sf-blue focus:outline-none focus:ring-2 focus:ring-sf-blue/30">
        <button type="submit" class="absolute bottom-1.5 right-1.5 inline-flex items-center justify-center rounded-lg bg-sf-blue px-3 py-1.5 text-xs font-medium leading-5 text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-2 focus:ring-sf-blue/50">Search</button>
    </div>
    @if (($filters['q'] ?? '') !== '' || $selectedGenres !== [])
        <a href="{{ $clearUrl }}" class="inline-flex shrink-0 items-center justify-center rounded-lg border border-sf-border px-3 text-sm font-medium text-gray-300 transition hover:bg-white/5">Clear</a>
    @endif
</form>
