@extends('admin.layout')

@section('title', 'Films')

@section('content')
    <div>
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-bold text-white">Films</h1>
                <p class="mt-1 text-sf-muted">Browse the catalogue and manage each film.</p>
            </div>
            <a href="{{ route('admin.films.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-950/40 transition hover:bg-red-500">＋ Add Film</a>
        </div>

        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="w-full lg:max-w-3xl">
                @include('components.film-search-form', ['action' => route('admin.films.index'), 'clearUrl' => route('admin.films.index'), 'filters' => $filters, 'genres' => $genres, 'accent' => 'red'])
            </div>
            <span class="text-sm text-sf-muted lg:ml-auto">{{ $films->total() }} films</span>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @forelse ($films as $film)
                <x-movie-poster-card :film="$film" :admin-mode="true" />
            @empty
                <p class="col-span-full rounded-xl border border-sf-border bg-sf-surface p-6 text-sf-muted">{{ ($filters['q'] ?? '') !== '' || $filters['genres'] !== [] ? 'No films match these search filters.' : 'No films have been added yet.' }}</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $films->links() }}</div>
    </div>
@endsection
