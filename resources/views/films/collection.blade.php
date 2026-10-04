<x-app-layout>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('films.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-sf-muted transition hover:text-white">← All Films</a>
        <header class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sf-text">Film collection</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $title }}</h1>
            </div>
            @if (isset($films))
                <p class="text-sm text-sf-muted">{{ $films->total() }} films</p>
            @else
                <p class="text-sm text-sf-muted">{{ $popularReviews->total() }} reviews</p>
            @endif
        </header>

        @if ($category === 'popular-reviews-this-week')
            @if ($popularReviews->isEmpty())
                <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-10 text-center text-sm text-sf-muted">No popular reviews this week yet.</div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($popularReviews as $review)
                        @include('films._popular-review-card', ['review' => $review])
                    @endforeach
                </div>
                <div class="mt-8">{{ $popularReviews->links() }}</div>
            @endif
        @elseif ($films->isEmpty())
            <div class="rounded-2xl border border-sf-border bg-sf-surface/70 px-5 py-10 text-center text-sm text-sf-muted">No films in this collection yet.</div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                @foreach ($films as $film)
                    <x-movie-poster-card :film="$film" />
                @endforeach
            </div>
            <div class="mt-8">{{ $films->links() }}</div>
        @endif
    </main>
</x-app-layout>
