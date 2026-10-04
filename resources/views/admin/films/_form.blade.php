<input type="hidden" name="tmdb_id" id="tmdb_id" value="{{ old('tmdb_id', $film->tmdb_id ?? '') }}">
<input type="hidden" name="original_title" id="original_title" value="{{ old('original_title', $film->original_title ?? '') }}">
<input type="hidden" name="tmdb_poster_path" id="tmdb_poster_path" value="{{ old('tmdb_poster_path', isset($film) && str_starts_with((string) $film->poster_path, '/') ? $film->poster_path : '') }}">

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $film->title ?? '') }}" required class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Synopsis</label>
        <textarea id="synopsis" name="synopsis" rows="4" class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">{{ old('synopsis', $film->synopsis ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Genre</label>
        <input id="genre" type="text" name="genre" value="{{ old('genre', $film->genre ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div>
        <label class="mb-2 block text-sm text-sf-muted">TMDB Release Date</label>
        <input id="release_date" type="date" name="release_date" value="{{ old('release_date', ($film ?? null)?->release_date?->format('Y-m-d') ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Release Year</label>
        <input id="release_year" type="number" name="release_year" value="{{ old('release_year', $film->release_year ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Cast</label>
        <input id="cast" type="text" name="cast" value="{{ old('cast', $film->cast ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#384B70] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div class="md:col-span-2 flex flex-col gap-5 rounded-xl border border-white/5 bg-[#507687] p-4 sm:flex-row sm:items-start">
        @if (isset($film) && $film->poster_path)
            <div class="shrink-0">
                <p class="mb-2 text-sm font-medium text-sf-muted">Current poster</p>
                <div class="h-56 w-40 overflow-hidden rounded-xl border border-white/10 bg-[#384B70] shadow-lg shadow-black/30">
                    <x-film-poster-image :film="$film" container-class="h-full w-full" :alt="$film->title.' current poster'" loading="eager" />
                </div>
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <label class="mb-2 block text-sm font-medium text-sf-muted">{{ isset($film) && $film->poster_path ? 'Replace poster' : 'Poster' }}</label>
            <input type="file" name="poster" accept="image/jpeg,image/png,image/webp" class="w-full rounded-xl border border-dashed border-white/10 bg-[#384B70] px-3 py-2.5 text-white file:mr-3 file:rounded-lg file:border-0 file:bg-[#B8001F] file:px-3 file:py-2 file:font-medium file:text-white">
            <p class="mt-2 text-xs text-sf-muted">Choose an image file to update the movie poster.</p>
        </div>
    </div>

    <div id="posterPreviewWrap" class="hidden md:col-span-2">
        <p class="mb-2 text-sm text-sf-muted">Selected poster preview</p>
        <img id="posterPreview" src="" alt="TMDB poster preview" class="h-56 w-40 rounded-xl border border-white/5 bg-[#384B70] object-contain">
        <p id="posterPreviewFallback" class="hidden mt-2 text-sm text-sf-muted">No poster is available for this film.</p>
    </div>
</div>

@error('title')
    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
@enderror
