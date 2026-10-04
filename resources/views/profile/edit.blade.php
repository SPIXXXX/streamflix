<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-sf-surface shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-sf-surface shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-sf-surface shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-sf-surface shadow sm:rounded-lg">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">My Lists</h2>

                <div class="mt-4 space-y-2">
                    @forelse ($lists as $list)
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-sf-bg p-3 rounded">
                            <a href="{{ route('lists.show', $list) }}" class="text-gray-900 dark:text-gray-100 hover:underline">
                                {{ $list->title }} <span class="text-sm text-gray-500">({{ $list->is_public ? 'Public' : 'Private' }} · {{ $list->films_count }} films)</span>
                            </a>
                            <div class="flex gap-3 text-sm">
                                <a href="{{ route('lists.edit', $list) }}" class="text-sf-text hover:underline">Edit</a>
                                <form action="{{ route('lists.destroy', $list) }}" method="POST" data-confirm data-confirm-title="Delete this list?" data-confirm-message="{{ $list->title }} and its saved movie entries will be permanently removed." data-confirm-label="Delete list">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">You haven't created any lists yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-sf-surface shadow sm:rounded-lg">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">My Reviews &amp; Ratings</h2>

                <div class="mt-4 space-y-2">
                    @forelse ($reviews as $review)
                        <div class="flex justify-between items-center bg-gray-50 dark:bg-sf-bg p-3 rounded">
                            <div>
                                <a href="{{ route('films.show', $review->film) }}" class="text-gray-900 dark:text-gray-100 hover:underline">
                                    {{ $review->film->title }}
                                </a>
                                <span class="text-sf-text ml-2">★ {{ $review->rating }}</span>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $review->comment }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <details class="text-sm">
                                    <summary class="cursor-pointer text-sf-text hover:underline">Edit</summary>
                                    <form action="{{ route('reviews.update', $review) }}" method="POST" class="mt-3 min-w-64 space-y-2">
                                        @csrf @method('PATCH')
                                        <label class="block text-xs text-gray-500">Rating (1–5)</label>
                                        <input type="number" name="rating" min="1" max="5" required value="{{ $review->rating }}" class="w-full rounded border-gray-300 dark:border-sf-border dark:bg-sf-bg dark:text-white">
                                        <label class="block text-xs text-gray-500">Review</label>
                                        <textarea name="comment" rows="3" maxlength="2000" class="w-full rounded border-gray-300 dark:border-sf-border dark:bg-sf-bg dark:text-white">{{ $review->comment }}</textarea>
                                        <button class="text-sf-text hover:underline">Save changes</button>
                                    </form>
                                </details>
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm data-confirm-title="Delete this review?" data-confirm-message="Your review and its reactions will be permanently removed." data-confirm-label="Delete review">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:underline text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">You haven't posted any reviews yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
