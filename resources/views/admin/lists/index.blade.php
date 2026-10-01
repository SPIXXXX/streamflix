@extends('admin.layout')

@section('title', 'Lists')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm font-semibold uppercase tracking-[.18em] text-rose-400">Content management</p><h1 class="mt-1 text-3xl font-bold text-white">Lists</h1><p class="mt-2 text-sm text-slate-400">Manage official movie collections curated from your catalog.</p></div>
        <a href="{{ route('admin.lists.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-950/30 transition hover:bg-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-800">＋ Create Official List</a>
    </header>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Total Lists', $stats['total']], ['Genre Lists', $stats['genres']], ['Theme Lists', $stats['themes']], ['Total Movies in Lists', $stats['films']]] as [$label, $value])
            <div class="rounded-lg border border-slate-700 bg-slate-900/80 px-4 py-3.5"><p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-1.5 text-2xl font-semibold text-white">{{ number_format($value) }}</p></div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.lists.index') }}" class="grid gap-2.5 rounded-xl border border-slate-700 bg-slate-900/70 p-3 sm:grid-cols-2 xl:grid-cols-[minmax(14rem,1fr)_135px_145px_145px_165px_auto_auto]">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search lists..." aria-label="Search lists" class="min-w-0 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white placeholder-slate-400 focus:border-rose-500 focus:ring-rose-500">
        <select name="type" aria-label="Filter by list type" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white focus:border-rose-500 focus:ring-rose-500"><option value="">All types</option><option value="genre" @selected(request('type') === 'genre')>Genre</option><option value="theme" @selected(request('type') === 'theme')>Theme</option></select>
        <select name="genre" aria-label="Filter by genre" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white focus:border-rose-500 focus:ring-rose-500"><option value="">All genres</option>@foreach ($genreClassifications as $genre)<option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>@endforeach</select>
        <select name="theme" aria-label="Filter by theme" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white focus:border-rose-500 focus:ring-rose-500"><option value="">All themes</option>@foreach ($themeClassifications as $theme)<option value="{{ $theme }}" @selected(request('theme') === $theme)>{{ $theme }}</option>@endforeach</select>
        <select name="sort" aria-label="Sort lists" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-white focus:border-rose-500 focus:ring-rose-500"><option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option><option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option><option value="most_films" @selected(request('sort') === 'most_films')>Most Movies</option><option value="alphabetical" @selected(request('sort') === 'alphabetical')>Alphabetical</option></select>
        <button class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600">Filter</button>
        @if (request()->hasAny(['q','type','genre','theme','sort']))<a href="{{ route('admin.lists.index') }}" class="rounded-lg px-3 py-2 text-center text-sm text-slate-300 hover:bg-slate-800">Clear</a>@endif
    </form>

    @if ($lists->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-700 bg-slate-900/50 px-6 py-14 text-center"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-800 text-slate-400"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 5.5A1.5 1.5 0 015.5 4H20v16H5.5A1.5 1.5 0 014 21.5v-16zM4 5.5A1.5 1.5 0 012.5 4H2v16h.5A1.5 1.5 0 014 21.5"/></svg></div><h2 class="mt-4 text-lg font-semibold text-white">{{ $stats['total'] === 0 ? 'No official lists yet' : 'No matching lists' }}</h2><p class="mx-auto mt-2 max-w-md text-sm text-slate-400">{{ $stats['total'] === 0 ? 'Create your first genre or theme collection using movies already in your catalog.' : 'Try another search or clear the filters.' }}</p>@if($stats['total'] === 0)<a href="{{ route('admin.lists.create') }}" class="mt-5 inline-flex rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-rose-500">＋ Create Official List</a>@endif</div>
    @else
        <section aria-label="Official movie lists" class="space-y-3">
            @foreach ($lists as $list)
                <article class="rounded-xl border border-slate-700 bg-slate-900/80 p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-slate-500 hover:shadow-lg hover:shadow-black/20 sm:p-5">
                    <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.lists.show', $list) }}" class="group/list-poster flex min-w-0 flex-1 flex-col items-center gap-3 rounded-lg text-center focus:outline-none focus:ring-2 focus:ring-rose-500 sm:flex-row sm:items-center sm:gap-5 sm:text-left">
                            <x-list-poster-collage :list="$list" :movies="$list->films" :limit="4" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><h2 class="max-w-full truncate text-base font-semibold text-white sm:text-lg">{{ $list->title }}</h2><span class="rounded-full bg-blue-950 px-2 py-0.5 text-[11px] font-medium text-blue-200">Official</span>@if($list->classification_type)<span class="rounded-full bg-violet-950 px-2 py-0.5 text-[11px] font-medium uppercase text-violet-200">{{ $list->classification_type }}</span>@endif @if($list->is_featured)<span class="rounded-full bg-amber-950 px-2 py-0.5 text-[11px] font-medium text-amber-200">Featured</span>@endif</div>
                            <p class="mt-1 line-clamp-2 max-w-3xl text-sm leading-5 text-slate-400">{{ $list->description ?: 'No description provided.' }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500"><span>{{ $list->classification ? $list->classification_type.': '.$list->classification : 'Unclassified' }}</span><span>{{ $list->films_count }} {{ \Illuminate\Support\Str::plural('movie', $list->films_count) }}</span><span>Updated {{ $list->updated_at->diffForHumans() }}</span></div>
                        </div></a>
                        <div class="flex shrink-0 flex-wrap items-center gap-1.5 border-t border-slate-800 pt-3 sm:justify-end sm:border-0 sm:pt-0">
                            <a href="{{ route('admin.lists.show', $list) }}" class="rounded-lg border border-slate-600 px-3 py-2 text-xs font-medium text-slate-200 transition hover:bg-slate-800">View</a>
                            <a href="{{ route('admin.lists.manage-films', $list) }}" class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-medium text-white transition hover:bg-slate-600">Manage Films</a>
                            <a href="{{ route('admin.lists.edit', $list) }}" class="rounded-lg border border-slate-600 px-3 py-2 text-xs font-medium text-slate-200 transition hover:bg-slate-800">Edit</a>
                            <form action="{{ route('admin.lists.destroy', $list) }}" method="POST" data-confirm data-confirm-title="Delete official list?" data-confirm-message="{{ $list->title }} and its film associations will be removed. Film records will remain." data-confirm-label="Delete list">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-red-900/70 px-3 py-2 text-xs font-medium text-red-300 transition hover:bg-red-950/40">Delete</button></form>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
        <div>{{ $lists->links() }}</div>
    @endif
</div>
@endsection
