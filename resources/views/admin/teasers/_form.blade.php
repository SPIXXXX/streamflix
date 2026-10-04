@php
    $selectedFilmId = old('film_id', data_get($teaser ?? null, 'film_id'));
    $selectedVideoUrl = old('video_url', data_get($teaser ?? null, 'video_url'));
    $selectedDescription = old('description', data_get($teaser ?? null, 'description'));
    $selectedReleaseDate = old('release_date', data_get($teaser ?? null, 'release_date')?->format('Y-m-d'));
@endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm text-sf-muted mb-2">Film</label>
        <select name="film_id" required class="w-full rounded-xl border border-white/5 bg-[#182337] px-3 py-2.5 text-white focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
            <option value="" class="bg-[#182337] text-sf-muted">Select a film</option>
            @foreach ($films as $film)
                <option value="{{ $film->id }}" @selected($selectedFilmId == $film->id) class="bg-[#182337] text-white">{{ $film->title }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Video URL (YouTube embed link)</label>
        <input type="url" name="video_url" value="{{ $selectedVideoUrl }}" required placeholder="https://www.youtube.com/embed/..." class="w-full rounded-xl border border-white/5 bg-[#182337] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Description</label>
        <textarea name="description" rows="4" class="w-full rounded-xl border border-white/5 bg-[#182337] px-3 py-2.5 text-white placeholder:text-sf-muted focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">{{ $selectedDescription }}</textarea>
    </div>

    <div>
        <label class="block text-sm text-sf-muted mb-2">Release Date</label>
        <input type="date" name="release_date" value="{{ $selectedReleaseDate }}" class="w-full rounded-xl border border-white/5 bg-[#182337] px-3 py-2.5 text-white focus:border-[#B8001F] focus:outline-none focus:ring-2 focus:ring-[#B8001F]/20">
    </div>
</div>

@error('film_id')
    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
@enderror
