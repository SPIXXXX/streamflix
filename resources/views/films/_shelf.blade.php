<section class="mb-10" aria-labelledby="{{ $id }}-title">
    <div class="mb-4 flex items-end justify-between gap-4">
        <div>
            <h2 id="{{ $id }}-title" class="text-xl font-semibold text-white sm:text-2xl">{{ $title }}</h2>
            <p class="mt-1 text-sm text-sf-muted">{{ $description }}</p>
        </div>
        <a href="{{ route('films.collections', $viewAll) }}" class="shrink-0 rounded-lg px-2 py-2 text-sm font-semibold text-sf-blue transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">View All <span aria-hidden="true">→</span></a>
    </div>
    @if ($films->isEmpty())
        <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-8 text-sm text-sf-muted">{{ $emptyMessage }}</div>
    @else
        <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden sm:mx-0 sm:gap-5 sm:px-0" role="region" aria-label="{{ $title }} films">
            @foreach ($films as $film)
                <div class="w-[42vw] max-w-52 shrink-0 snap-start sm:w-44 md:w-48 lg:w-52">
                    <x-movie-poster-card :film="$film" />
                </div>
            @endforeach
        </div>
    @endif
</section>
