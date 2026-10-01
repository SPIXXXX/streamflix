<div>
    <label class="block text-sm text-gray-300 mb-1">Title</label>
    <input type="text" name="title" value="{{ old('title', $list->title ?? '') }}" required class="w-full rounded bg-sf-bg border-gray-700 text-white px-3 py-2">
</div>
<div>
    <label class="block text-sm text-gray-300 mb-1">Description</label>
    <textarea name="description" rows="3" class="w-full rounded bg-sf-bg border-gray-700 text-white px-3 py-2">{{ old('description', $list->description ?? '') }}</textarea>
</div>
<div class="flex items-center gap-2">
    <input type="checkbox" name="is_public" id="is_public" value="1" @checked(old('is_public', $list->is_public ?? true)) class="rounded bg-sf-bg border-gray-700">
    <label for="is_public" class="text-sm text-gray-300">Make this list public</label>
</div>
@error('title')
    <p class="text-red-400 text-sm">{{ $message }}</p>
@enderror
