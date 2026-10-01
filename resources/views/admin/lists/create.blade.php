@extends('admin.layout')

@section('title', 'Create Official List')

@section('content')
<div class="mx-auto max-w-7xl space-y-6" x-data="adminListFilmPicker({ endpoint: @js(route('admin.lists.matching-films')), metadataEndpoint: @js(route('admin.lists.films.metadata', ['film' => 'FILM_ID'])), classificationType: @js(old('classification_type', '')), classifications: @js($selectedClassifications), genres: @js($genres), themes: @js($themes), selected: @js($selectedFilmData) })" x-init="load()">
    <nav aria-label="Breadcrumb" class="text-sm text-slate-400"><a href="{{ route('admin.lists.index') }}" class="hover:text-white">Lists</a><span class="px-2 text-slate-600">/</span><span class="text-slate-200">Create Official List</span></nav>
    <header><p class="text-sm font-semibold uppercase tracking-[.16em] text-rose-300">Admin collection</p><h1 class="mt-1 text-3xl font-bold text-white">Create Official List</h1><p class="mt-2 text-sm text-slate-400">Build an official movie collection from movies already available in your database.</p></header>
    <form method="POST" action="{{ route('admin.lists.store') }}" @submit="saving = true" class="space-y-6">@csrf
        <section class="rounded-xl border border-slate-700 bg-slate-900/80 p-5 shadow-sm sm:p-6"><div class="mb-5"><h2 class="text-lg font-semibold text-white">Basic Information & Collection Type</h2><p class="mt-1 text-sm text-slate-400">Choose a genre or theme to load matching movies automatically.</p></div>@php($formIdPrefix = 'create-list')@include('admin.lists._form')</section>
        @include('admin.lists._film-picker')
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('admin.lists.index') }}" class="rounded-lg border border-slate-600 px-5 py-2.5 text-center text-sm font-medium text-slate-200 hover:bg-slate-800">Cancel</a><button type="submit" :disabled="saving" class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-950/30 transition hover:bg-rose-500 focus:ring-4 focus:ring-rose-800 disabled:cursor-wait disabled:opacity-60" x-text="saving ? 'Saving…' : 'Create Official List'"></button></div>
    </form>
</div>
@endsection
