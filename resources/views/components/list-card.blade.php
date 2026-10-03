@props([
    'title',
    'href',
    'description' => null,
    'count' => 0,
    'visibility' => null,
    'owner' => null,
    'films' => collect(),
    'icon' => 'list',
    'official' => false,
    'category' => null,
    'posterMode' => false,
    'list' => null,
])

<a href="{{ $href }}" aria-label="Open {{ $title }}, {{ $count }} movies" @class(['group flex h-full flex-col rounded-2xl border border-sf-border bg-sf-surface p-4 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-sf-blue/50 hover:bg-sf-surface-light hover:shadow-glow-blue focus:outline-none focus:ring-2 focus:ring-sf-blue sm:p-5', 'group/list-poster' => $posterMode])>
    <div class="flex items-start justify-between gap-3">
        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl {{ $icon === 'heart' ? 'bg-pink-500/10 text-pink-300' : ($official ? 'bg-amber-400/10 text-amber-300' : 'bg-sf-blue/10 text-sf-blue') }}">
            @if ($icon === 'heart')
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
            @elseif ($official)
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
            @else
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5.5A1.5 1.5 0 0 1 5.5 4H20v16H5.5A1.5 1.5 0 0 0 4 21.5v-16Zm0 0A1.5 1.5 0 0 0 2.5 4H2v16h.5A1.5 1.5 0 0 1 4 21.5M8 9h8m-8 4h8"/></svg>
            @endif
        </span>
        <div class="flex flex-wrap justify-end gap-1.5">
            @if ($category)
                <span class="inline-flex items-center rounded-full border border-sf-blue/20 bg-sf-blue/10 px-2.5 py-1 text-[11px] font-medium text-sf-blue">{{ $category }}</span>
            @endif
            @if ($visibility)
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $visibility === 'Private' ? 'border-sf-border text-sf-muted' : 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' }}">{{ $visibility }}</span>
            @endif
        </div>
    </div>

    <div class="mt-4 min-h-16">
        <h3 class="truncate text-lg font-semibold text-white group-hover:text-sf-blue">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 line-clamp-2 min-h-10 text-sm leading-5 text-sf-muted">{{ $description }}</p>
        @else
            <p class="mt-1 min-h-10 text-sm leading-5 text-sf-muted">{{ $official ? 'A curated collection from the CINEVAULT team.' : 'A collection of movies.' }}</p>
        @endif
    </div>

    @if ($posterMode && $list)
        <x-list-poster-collage :list="$list" :movies="$films" :limit="4" presentation="client" />
    @else
        <div class="mt-4 grid h-20 grid-cols-4 gap-2" aria-hidden="true">
            @forelse ($films->take(4) as $film)
                <div class="overflow-hidden rounded-lg border border-white/5 bg-sf-bg">
                    <x-film-poster-image :film="$film" container-class="h-full w-full transition duration-200 group-hover:scale-105" :placeholder-text="$film->title" alt="" loading="lazy" />
                </div>
            @empty
                @for ($index = 0; $index < 4; $index++)
                    <div class="flex items-center justify-center rounded-lg border border-dashed border-sf-border bg-sf-bg/70">
                        <svg class="h-5 w-5 text-sf-muted/50" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m8 6 10 6-10 6V6Z"/></svg>
                    </div>
                @endfor
            @endforelse
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3 border-t border-sf-border pt-3">
        <span class="text-sm font-medium text-sf-text">{{ $count }} {{ $count === 1 ? 'movie' : 'movies' }}</span>
        @if ($owner)
            <span class="flex min-w-0 items-center gap-2 text-xs text-sf-muted">
                <x-user-avatar :user="$owner" size="h-6 w-6" text-size="text-[10px]" :fallback-on-error="$posterMode" />
                <span class="max-w-24 truncate">{{ $owner->name }}</span>
            </span>
        @elseif ($official)
            <span class="text-xs font-medium text-amber-300">Official</span>
        @endif
    </div>
    @if ($posterMode)
        <span class="mt-3 inline-flex items-center justify-end gap-1 text-sm font-semibold text-sf-blue transition group-hover:gap-2 group-hover:text-white">View List <span aria-hidden="true">→</span></span>
    @endif
</a>
