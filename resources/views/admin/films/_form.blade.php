<input type="hidden" name="tmdb_id" id="tmdb_id" value="{{ old('tmdb_id', $film->tmdb_id ?? '') }}">
<input type="hidden" name="original_title" id="original_title" value="{{ old('original_title', $film->original_title ?? '') }}">
<input type="hidden" name="tmdb_poster_path" id="tmdb_poster_path" value="{{ old('tmdb_poster_path', isset($film) && str_starts_with((string) $film->poster_path, '/') ? $film->poster_path : '') }}">

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Title</label>
        <input id="title" type="text" name="title" value="{{ old('title', $film->title ?? '') }}" required class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Synopsis</label>
        <textarea id="synopsis" name="synopsis" rows="4" class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">{{ old('synopsis', $film->synopsis ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Genre</label>
        <input id="genre" type="text" name="genre" value="{{ old('genre', $film->genre ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">
    </div>

    <div>
        <label class="mb-2 block text-sm text-sf-muted">TMDB Release Date</label>
        <input id="release_date" type="date" name="release_date" value="{{ old('release_date', ($film ?? null)?->release_date?->format('Y-m-d') ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Release Year</label>
        <input id="release_year" type="number" name="release_year" value="{{ old('release_year', $film->release_year ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Cast</label>
        <input id="cast" type="text" name="cast" value="{{ old('cast', $film->cast ?? '') }}" class="w-full rounded-xl border border-white/5 bg-[#160c1f] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#c81f3b] focus:outline-none focus:ring-2 focus:ring-[#c81f3b]/20">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm text-sf-muted mb-2">Poster</label>
        <input type="file" name="poster" class="w-full rounded-xl border border-dashed border-white/5 bg-[#160c1f] px-3 py-2.5 text-white file:mr-3 file:rounded file:border-0 file:bg-[#c81f3b] file:px-3 file:py-2 file:text-white file:font-medium">
    </div>

    <div id="posterPreviewWrap" class="hidden md:col-span-2">
        <p class="mb-2 text-sm text-sf-muted">Selected poster preview</p>
        <img id="posterPreview" src="" alt="TMDB poster preview" class="h-56 w-40 rounded-xl border border-white/5 bg-[#160c1f] object-contain">
        <p id="posterPreviewFallback" class="hidden mt-2 text-sm text-sf-muted">No poster is available for this film.</p>
    </div>
</div>

@error('title')
    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
@enderror
