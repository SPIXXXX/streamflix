@extends('admin.layout')

@section('title', 'Edit Official List')

@section('content')
<div class="mx-auto max-w-7xl space-y-6" x-data="adminListFilmPicker({ endpoint: @js(route('admin.lists.matching-films')), metadataEndpoint: @js(route('admin.lists.films.metadata', ['film' => 'FILM_ID'])), classificationType: @js(old('classification_type', $list->classification_type)), classifications: @js($selectedClassifications), genres: @js($genres), themes: @js($themes), listId: @js($list->id), selected: @js($selectedFilmData) })" x-init="load()">
    <nav aria-label="Breadcrumb" class="text-sm text-slate-400"><a href="{{ route('admin.lists.index') }}" class="hover:text-white">Lists</a><span class="px-2 text-slate-600">/</span><a href="{{ route('admin.lists.show', $list) }}" class="hover:text-white">{{ $list->title }}</a><span class="px-2 text-slate-600">/</span><span class="text-slate-200">Edit</span></nav>
    <header><h1 class="text-3xl font-bold text-white">Edit Official List</h1><p class="mt-2 text-sm text-slate-400">Update its details and synchronize the selected movies.</p></header>
    <form method="POST" action="{{ route('admin.lists.update', $list) }}" @submit="saving = true" class="space-y-6">@csrf @method('PUT')
        <section class="rounded-xl border border-slate-700 bg-slate-900/80 p-5 shadow-sm sm:p-6"><div class="mb-5"><h2 class="text-lg font-semibold text-white">Basic Information & Collection Type</h2><p class="mt-1 text-sm text-slate-400">The current movies are preselected. Change the classification or movie selection as needed.</p></div>@php($formIdPrefix = 'edit-list')@include('admin.lists._form')</section>
        @include('admin.lists._film-picker')
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('admin.lists.show', $list) }}" class="rounded-lg border border-slate-600 px-5 py-2.5 text-center text-sm font-medium text-slate-200 hover:bg-slate-800">Cancel</a><button type="submit" :disabled="saving" class="rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-500 disabled:cursor-wait disabled:opacity-60" x-text="saving ? 'Saving…' : 'Save Changes'"></button></div>
    </form>
</div>
@endsection
