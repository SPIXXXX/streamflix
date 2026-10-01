<article class="rounded-2xl border border-sf-border bg-sf-surface p-5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('films.show', $review->film) }}" class="font-semibold text-white hover:text-sf-blue">{{ $review->film->title }}</a>
            <p class="mt-1 flex items-center gap-2 text-xs text-sf-muted"><x-user-avatar :user="$review->user" size="h-6 w-6" text-size="text-[10px]" /> Review by {{ $review->user->name }}</p>
        </div>
        <span class="shrink-0 text-sm font-semibold text-amber-300">★ {{ $review->rating }}/5</span>
    </div>
    @if ($review->comment)
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-gray-300">{{ $review->comment }}</p>
    @endif
    <p class="mt-4 text-xs text-sf-muted">{{ $review->weekly_agree_count }} agrees this week</p>
</article>
