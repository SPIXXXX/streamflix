@extends('admin.layout')

@section('title', $list->title)

@section('content')
<div class="mx-auto max-w-7xl space-y-7">
    <a href="{{ route('admin.lists.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white">← Back to Lists</a>

    <section class="rounded-2xl border border-slate-700 bg-slate-900/80 p-5 sm:p-7">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
            <div class="mx-auto shrink-0 sm:mx-0"><x-list-poster-collage :list="$list" :movies="$previewFilms" :limit="4" /></div>
            <div class="min-w-0">
                <div class="mb-3 flex flex-wrap gap-2"><span class="rounded-full bg-blue-950 px-2.5 py-1 text-xs font-semibold text-sf-text">Official List</span>@if($list->classification_type)<span class="rounded-full bg-violet-950 px-2.5 py-1 text-xs font-semibold uppercase text-violet-200">{{ $list->classification_type }} · {{ $list->classification }}</span>@endif @if($list->is_featured)<span class="rounded-full bg-amber-950 px-2.5 py-1 text-xs font-semibold text-amber-200">Featured</span>@endif</div>
                <h1 class="text-3xl font-bold text-white">{{ $list->title }}</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">{{ $list->description ?: 'No description provided.' }}</p>
                <p class="mt-4 text-xs text-slate-400">{{ $list->films_count }} {{ \Illuminate\Support\Str::plural('movie', $list->films_count) }} <span class="px-1.5">·</span> Created {{ $list->created_at->format('M j, Y') }} <span class="px-1.5">·</span> Updated {{ $list->updated_at->format('M j, Y') }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2"><a href="{{ route('admin.lists.edit', $list) }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit List</a><a href="{{ route('admin.lists.manage-films', $list) }}" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Manage Films</a></div>
        </div>
    </section>

    <section class="space-y-4" aria-labelledby="list-movies-heading">
        <div><h2 id="list-movies-heading" class="text-xl font-bold text-white">Movies in This List</h2><p class="mt-1 text-sm text-slate-400">Every movie attached to this collection. Select a poster to view the movie details.</p></div>
        @if($currentFilms->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-700 bg-slate-900/50 p-8 text-center"><p class="text-slate-300">This list does not contain any movies yet.</p><a href="{{ route('admin.lists.manage-films', $list) }}" class="mt-4 inline-flex rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Manage Films</a></div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">@foreach($currentFilms as $film)<x-movie-poster-card :film="$film" :film-url="route('admin.films.show', $film)" />@endforeach</div>
            <div>{{ $currentFilms->links() }}</div>
        @endif
    </section>
</div>
@endsection
