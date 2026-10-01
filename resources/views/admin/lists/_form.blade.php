<div class="space-y-5">
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Basic information</h3>
        <label for="{{ $formIdPrefix }}-title" class="mb-2 mt-3 block text-sm font-medium text-white">List title <span class="text-rose-300">*</span></label>
        <input id="{{ $formIdPrefix }}-title" type="text" name="title" value="{{ old('title', $list->title ?? '') }}" placeholder="e.g. Essential Science Fiction" required maxlength="255" class="w-full rounded-lg border border-slate-600 bg-slate-800 p-2.5 text-sm text-white placeholder-slate-400 focus:border-rose-500 focus:ring-rose-500">
        @error('title')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="{{ $formIdPrefix }}-description" class="mb-2 block text-sm font-medium text-white">Description <span class="font-normal text-slate-400">(optional)</span></label>
        <textarea id="{{ $formIdPrefix }}-description" name="description" rows="3" placeholder="Tell members what connects the films in this collection." class="w-full rounded-lg border border-slate-600 bg-slate-800 p-2.5 text-sm text-white placeholder-slate-400 focus:border-rose-500 focus:ring-rose-500">{{ old('description', $list->description ?? '') }}</textarea>
        @error('description')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
    </div>

    <fieldset>
        <legend class="mb-2 block text-sm font-medium text-white">Collection type <span class="text-rose-300">*</span></legend>
        <div class="grid grid-cols-2 gap-3">
            @foreach (['genre' => 'Genre', 'theme' => 'Theme'] as $typeValue => $typeLabel)
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-700 bg-slate-800/70 p-3 transition hover:border-slate-500" :class="classificationType === '{{ $typeValue }}' ? 'border-rose-500 bg-rose-950/20 ring-1 ring-rose-500/30' : ''">
                    <input type="radio" name="classification_type" value="{{ $typeValue }}" x-model="classificationType" @change="classificationChanged()" required class="h-4 w-4 border-slate-500 bg-slate-700 text-rose-600 focus:ring-rose-500">
                    <span class="text-sm font-medium text-white">{{ $typeLabel }}</span>
                </label>
            @endforeach
        </div>
        @error('classification_type')<p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>@enderror
    </fieldset>

    @error('classifications')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
    @error('classifications.*')<p class="text-sm text-red-400">{{ $message }}</p>@enderror

    <label for="{{ $formIdPrefix }}-is-featured" class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-700 bg-slate-800/70 p-4">
        <input id="{{ $formIdPrefix }}-is-featured" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $list->is_featured ?? false)) class="mt-0.5 h-4 w-4 rounded border-slate-500 bg-slate-700 text-rose-600 focus:ring-rose-500">
        <span><span class="block text-sm font-medium text-white">Feature this list</span><span class="mt-1 block text-xs text-slate-400">Featured official lists appear in the client discovery page.</span></span>
    </label>
</div>
