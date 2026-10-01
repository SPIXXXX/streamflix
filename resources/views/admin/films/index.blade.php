@extends('admin.layout')

@section('title', 'Films')

@section('content')
    <div x-data="{ search: '', genre: 'all' }">
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h1 class="text-3xl font-bold text-white">Films</h1>
                <p class="mt-1 text-sf-muted">Browse the catalogue and manage each film.</p>
            </div>
            <a href="{{ route('admin.films.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-950/40 transition hover:bg-red-500">＋ Add Film</a>
        </div>

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="sr-only" for="filmSearch">Search films</label>
            <input id="filmSearch" x-model="search" type="search" placeholder="Search films..." class="w-full rounded-full border border-sf-border bg-sf-surface px-4 py-2 text-sm text-white placeholder:text-sf-muted focus:border-red-500 focus:ring-red-500 sm:max-w-sm">
            <label class="sr-only" for="genreFilter">Filter by genre</label>
            <select id="genreFilter" x-model="genre" class="rounded-full border border-sf-border bg-sf-surface px-4 py-2 text-sm text-white focus:border-red-500 focus:ring-red-500 sm:w-auto">
                <option value="all">All genres</option>
                @foreach ($films->getCollection()->pluck('genre')->filter()->unique()->sort() as $availableGenre)
                    <option value="{{ strtolower($availableGenre) }}">{{ $availableGenre }}</option>
                @endforeach
            </select>
            <span class="text-sm text-sf-muted sm:ml-auto">{{ $films->total() }} films</span>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @forelse ($films as $film)
                <div x-show="(search === '' || @js(strtolower($film->title)).includes(search.toLowerCase())) && (genre === 'all' || @js(strtolower($film->genre ?? '')).includes(genre))">
                    <x-movie-poster-card :film="$film" :admin-mode="true" />
                </div>
            @empty
                <p class="col-span-full rounded-xl border border-sf-border bg-sf-surface p-6 text-sf-muted">No films have been added yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $films->links() }}</div>
    </div>
@endsection
