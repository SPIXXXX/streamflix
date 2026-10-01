@props(['list', 'movies', 'limit' => 4, 'presentation' => 'admin'])

@php($visibleMovies = $movies->take($limit)->values())
@php($movieCount = $list->films_count ?? $movies->count())

@if ($presentation === 'client')
    @php($visibleCount = $visibleMovies->count())
    @php($mobileExtraCount = max(0, $movieCount - min($visibleCount, 3)))
    @php($desktopExtraCount = max(0, $movieCount - $visibleCount))
    <div class="group/list-poster relative isolate h-48 w-full overflow-visible bg-transparent sm:h-52" role="img" aria-label="{{ $movieCount }} {{ \Illuminate\Support\Str::plural('movie', $movieCount) }} in {{ $list->title }}">
        @if ($visibleMovies->isEmpty())
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-center">
                <span class="flex h-14 w-14 items-center justify-center text-sf-muted">
                    <svg class="h-7 w-7" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m8 6 10 6-10 6V6Zm-4-2h16v16H4z"/></svg>
                </span>
                <span class="text-xs font-medium text-sf-muted">No movies yet</span>
            </div>
        @else
            @php($posterCount = $visibleCount)
            @foreach ($visibleMovies as $film)
                @php($position = match ($posterCount) {
                    1 => 50,
                    2 => [43, 57][$loop->index],
                    3 => [32, 50, 68][$loop->index],
                    default => [22, 41, 59, 78][$loop->index],
                })
                @php($rotation = match ($posterCount) {
                    1 => 0,
                    2 => [-7, 7][$loop->index],
                    3 => [-8, 0, 8][$loop->index],
                    default => [-9, -3, 3, 9][$loop->index],
                })
                <div @class([
                    'absolute top-5 h-36 w-[4.5rem] -translate-x-1/2 overflow-hidden rounded-xl border border-white/20 bg-slate-800 shadow-xl shadow-black/50 transition duration-300 ease-out group-hover/list-poster:-translate-y-2 group-hover/list-poster:shadow-2xl sm:top-4 sm:h-40 sm:w-[5.5rem]',
                    'hidden md:block' => $loop->index === 3,
                ]) style="left: {{ $position }}%; z-index: {{ 10 + $loop->index }}; rotate: {{ $rotation }}deg">
                    <x-film-poster-image :film="$film" container-class="h-full w-full transition duration-300 group-hover/list-poster:scale-[1.035]" :alt="$film->title.' poster'" />
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-black/95 via-black/70 to-transparent px-1.5 pb-1.5 pt-6 text-[9px] font-medium leading-tight text-white opacity-0 transition duration-200 group-hover/list-poster:translate-y-0 group-hover/list-poster:opacity-100">{{ $film->title }}</div>
                </div>
            @endforeach
            @if ($mobileExtraCount > 0)
                <span class="absolute bottom-3 right-3 z-30 rounded-full border border-white/10 bg-slate-950/90 px-2.5 py-1 text-xs font-semibold text-white shadow md:hidden">+{{ $mobileExtraCount }}</span>
            @endif
            @if ($desktopExtraCount > 0)
                <span class="absolute bottom-3 right-3 z-30 hidden rounded-full border border-white/10 bg-slate-950/90 px-2.5 py-1 text-xs font-semibold text-white shadow md:inline-flex">+{{ $desktopExtraCount }}</span>
            @endif
        @endif
    </div>
@else

<div class="group/list-poster relative h-36 w-60 shrink-0 sm:h-40 sm:w-72" aria-label="Movie poster collage for {{ $list->title }}">
    @forelse($visibleMovies as $film)
        @php($offset = $loop->index * 3.25)
        @php($rotation = ($loop->index % 2 === 0 ? -1 : 1) * (3 - $loop->index))
        <div @class(['absolute top-2 h-28 w-[4.75rem] overflow-hidden rounded-lg border border-white/15 bg-slate-800 shadow-lg shadow-black/40 transition duration-300 ease-out group-hover/list-poster:-translate-y-2 group-hover/list-poster:rotate-0 group-hover/list-poster:shadow-xl sm:top-1 sm:h-32 sm:w-[5.25rem]', 'hidden sm:block' => $loop->index === 3]) style="left: {{ $offset }}rem; z-index: {{ 10 + $loop->index }}; transform: rotate({{ $rotation }}deg)">
            <x-film-poster-image :film="$film" container-class="h-full w-full" :alt="$film->title.' poster'" />
            <div class="pointer-events-none absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-black/95 via-black/70 to-transparent px-1.5 pb-1.5 pt-5 text-[9px] font-medium leading-tight text-white opacity-0 transition duration-200 group-hover/list-poster:translate-y-0 group-hover/list-poster:opacity-100">{{ $film->title }}</div>
        </div>
    @empty
        <div class="absolute left-0 top-2 flex h-28 w-[4.75rem] items-center justify-center rounded-lg border border-dashed border-slate-600 bg-slate-800 p-2 text-center text-[10px] text-slate-500 sm:top-1 sm:h-32 sm:w-[5.25rem]">No Poster</div>
    @endforelse
    @if($movieCount > 0)
        <span class="absolute bottom-0 right-0 z-30 rounded-full border border-white/10 bg-slate-950/90 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-200 shadow">{{ $movieCount }} {{ \Illuminate\Support\Str::plural('movie', $movieCount) }}</span>
    @endif
</div>
@endif
