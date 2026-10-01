@extends('admin.layout')

@section('title', 'Profile')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Profile</h1>
        <p class="mt-1 text-sm text-neutral-400">Manage your administrator account and personal activity.</p>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-neutral-800 bg-[#111014] p-5 sm:p-7">
            <div class="max-w-xl">@include('profile.partials.update-profile-information-form')</div>
        </section>
        <section class="rounded-xl border border-neutral-800 bg-[#111014] p-5 sm:p-7">
            <div class="max-w-xl">@include('profile.partials.update-password-form')</div>
        </section>
        <section class="rounded-xl border border-neutral-800 bg-[#111014] p-5 sm:p-7">
            <div class="max-w-xl">@include('profile.partials.delete-user-form')</div>
        </section>

        <section class="rounded-xl border border-neutral-800 bg-[#111014] p-5 sm:p-7">
            <h2 class="text-lg font-semibold text-white">My Bucket Lists</h2>
            <div class="mt-4 divide-y divide-neutral-800">
                @forelse ($lists as $list)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <a href="{{ route('lists.show', $list) }}" class="text-sm text-neutral-200 hover:text-red-300">{{ $list->title }} <span class="text-neutral-500">({{ $list->is_public ? 'Public' : 'Private' }} · {{ $list->films_count }} films)</span></a>
                        <div class="flex items-center gap-4 text-sm">
                            <a href="{{ route('lists.edit', $list) }}" class="text-neutral-300 hover:text-white">Edit</a>
                            <form action="{{ route('lists.destroy', $list) }}" method="POST" data-confirm data-confirm-title="Delete this list?" data-confirm-message="{{ $list->title }} and its saved movie entries will be permanently removed." data-confirm-label="Delete list">@csrf @method('DELETE')<button class="text-red-400 hover:text-red-300">Delete</button></form>
                        </div>
                    </div>
                @empty
                    <p class="py-3 text-sm text-neutral-400">You haven’t created any lists yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-neutral-800 bg-[#111014] p-5 sm:p-7">
            <h2 class="text-lg font-semibold text-white">My Reviews &amp; Ratings</h2>
            <div class="mt-4 divide-y divide-neutral-800">
                @forelse ($reviews as $review)
                    <div class="flex flex-wrap items-start justify-between gap-4 py-4">
                        <div>
                            <a href="{{ route('films.show', $review->film) }}" class="font-medium text-neutral-200 hover:text-red-300">{{ $review->film->title }}</a>
                            <span class="ml-2 text-amber-300">★ {{ $review->rating }}</span>
                            <p class="mt-1 text-sm text-neutral-400">{{ $review->comment }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <details>
                                <summary class="cursor-pointer text-sm text-neutral-300 hover:text-white">Edit</summary>
                                <form action="{{ route('reviews.update', $review) }}" method="POST" class="mt-3 w-64 space-y-2">@csrf @method('PATCH')<input type="number" name="rating" min="1" max="5" required value="{{ $review->rating }}" class="w-full rounded-lg border-neutral-700 bg-[#09070d] text-white"><textarea name="comment" rows="3" maxlength="2000" class="w-full rounded-lg border-neutral-700 bg-[#09070d] text-white">{{ $review->comment }}</textarea><button class="text-sm text-red-400 hover:text-red-300">Save changes</button></form>
                            </details>
                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm data-confirm-title="Delete this review?" data-confirm-message="Your review and its reactions will be permanently removed." data-confirm-label="Delete review">@csrf @method('DELETE')<button class="text-sm text-red-400 hover:text-red-300">Delete</button></form>
                        </div>
                    </div>
                @empty
                    <p class="py-3 text-sm text-neutral-400">You haven’t posted any reviews yet.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
