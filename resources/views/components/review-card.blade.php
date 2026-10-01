@props([
    'review',
    'showAuthor' => true,
    'showDate' => false,
])

<article class="border-b border-gray-700 py-4">
    @if ($showAuthor)
        <div class="flex items-center gap-2">
            <x-user-avatar :user="$review->user" size="h-7 w-7" text-size="text-xs" />
            <p class="text-white font-semibold">{{ $review->user->name }} <span class="text-blue-400">★ {{ $review->rating }}</span></p>
        </div>
    @else
        <p class="text-white font-semibold">★ {{ $review->rating }}</p>
    @endif
    <p class="mt-1">
        <a href="{{ route('films.show', $review->film) }}" class="font-semibold text-white hover:underline">{{ $review->film->title }}</a>
        @if ($showDate)
            <span class="ml-2 text-xs text-sf-muted">{{ $review->created_at?->format('M j, Y') }}</span>
        @endif
    </p>
    @if ($review->comment)
        <p class="mt-1 text-gray-300">{{ $review->comment }}</p>
    @endif

    @auth
        <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
            <button type="button" class="reaction inline-flex items-center gap-1 rounded px-1 py-1 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50" data-review-id="{{ $review->id }}" data-reaction="agree" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Agree with this review">
                <span aria-hidden="true">👍</span>
                <span class="reaction-count reaction-agree-{{ $review->id }}">{{ $review->agree_count }}</span>
            </button>
            <button type="button" class="reaction inline-flex items-center gap-1 rounded px-1 py-1 transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50" data-review-id="{{ $review->id }}" data-reaction="disagree" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Disagree with this review">
                <span aria-hidden="true">👎</span>
                <span class="reaction-count reaction-disagree-{{ $review->id }}">{{ $review->disagree_count }}</span>
            </button>
        </div>
    @endauth
</article>
