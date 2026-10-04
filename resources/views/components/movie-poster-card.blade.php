@props([
    'film',
    'adminMode' => false,
    'isFavorite' => false,
    'removeUrl' => null,
    'removeMethod' => 'DELETE',
    'removeLabel' => null,
    'filmUrl' => null,
])

<article class="group min-w-0">
    <a href="{{ $filmUrl ?: ($adminMode ? route('admin.films.show', $film) : route('films.show', $film)) }}" class="block min-w-0">
        <div class="relative aspect-[2/3] overflow-hidden rounded-2xl border border-sf-border bg-sf-surface shadow-lg shadow-black/20 transition duration-300 group-hover:-translate-y-1 group-hover:border-sf-blue/60 group-hover:shadow-xl group-hover:shadow-sf-blue/10">
            <x-film-poster-image :film="$film" :absolute="true" container-class="transition duration-500 group-hover:scale-[1.035]" />
            @if ($film->genre)
                <span class="absolute left-2 top-2 max-w-[calc(100%-1rem)] truncate rounded-full border border-white/10 bg-black/65 px-2.5 py-1 text-[10px] font-medium text-white backdrop-blur">{{ \Illuminate\Support\Str::before($film->genre, ',') }}</span>
            @endif
            @if ($isFavorite)
                <span class="absolute right-2 top-2 rounded-full border border-white/10 bg-pink-500/90 p-2 text-white shadow" aria-label="Favorited">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-8-4.7-8-11a4.5 4.5 0 0 1 8-2.9A4.5 4.5 0 0 1 20 10c0 6.3-8 11-8 11Z"/></svg>
                </span>
            @endif
        </div>
        <div class="mt-2.5 flex items-start justify-between gap-2 px-1 pb-3">
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold text-gray-100 transition group-hover:text-sf-blue">{{ $film->title }}</h3>
                <p class="mt-0.5 text-xs text-sf-muted">{{ $film->release_year ?: $film->release_date?->format('Y') ?: 'Year unavailable' }}</p>
            </div>
            @if (($film->reviews_count ?? 0) > 0)
                <span class="shrink-0 text-xs font-semibold text-amber-300" title="Member rating">★ {{ number_format((float) ($film->reviews_avg_rating ?? 0), 1) }}</span>
            @endif
        </div>
    </a>

    @if ($adminMode)
        <div class="grid grid-cols-1 gap-2 border-t border-sf-border px-1 pt-3 sm:grid-cols-2">
            <a href="{{ route('admin.films.edit', $film) }}" class="inline-flex h-10 min-w-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-sf-blue/25 bg-sf-blue/10 px-3 text-sm font-semibold text-blue-200 transition hover:border-sf-blue/50 hover:bg-sf-blue/20 hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">
                <svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14 5 5 5M4 20l4.5-1 11-11a2.12 2.12 0 0 0-3-3l-11 11L4 20Z"/></svg>
                <span>Edit</span>
            </a>
            <form action="{{ route('admin.films.destroy', $film) }}" method="POST" class="w-full min-w-0" data-confirm data-confirm-title="Delete this film?" data-confirm-message="{{ $film->title }} will be permanently removed. This also deletes its associated reviews, ratings, and list entries." data-confirm-label="Delete film">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-red-500/25 bg-red-500/10 px-3 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500/20 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    <svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 4v6m4-6v6M9 7V4h6v3m-9 0 1 13h10l1-13"/></svg>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    @elseif ($removeUrl)
        <div class="border-t border-sf-border px-3 py-2">
            <form action="{{ $removeUrl }}" method="POST" data-confirm data-confirm-title="{{ $removeMethod === 'POST' ? 'Remove from favorites?' : 'Remove this movie?' }}" data-confirm-message="{{ $film->title }} will be removed from {{ $removeMethod === 'POST' ? 'your favorites' : 'this list' }}." data-confirm-label="{{ $removeMethod === 'POST' ? 'Remove favorite' : 'Remove movie' }}">
                @csrf
                @if ($removeMethod !== 'POST')
                    @method($removeMethod)
                @endif
                <button type="submit" class="text-sm font-medium text-red-300 transition hover:text-red-200">{{ $removeLabel ?: 'Remove movie' }}</button>
            </form>
        </div>
    @endif
</article>
