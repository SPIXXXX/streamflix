@props([
    'review',
    'showAuthor' => true,
    'showDate' => false,
])

<article id="review-{{ $review->id }}" class="border-b border-sf-border py-5 last:border-b-0 sm:py-6">
    <header class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            @if ($showAuthor)
                <x-user-avatar :user="$review->user" size="h-10 w-10" text-size="text-sm" />
            @endif
            <div class="min-w-0">
                @if ($showAuthor)
                    <p class="truncate text-sm font-semibold text-white">{{ $review->user->name }}</p>
                @else
                    <a href="{{ route('films.show', $review->film) }}" class="truncate text-sm font-semibold text-white hover:text-sf-blue hover:underline">{{ $review->film->title }}</a>
                @endif
                @if ($showDate)
                    <p class="mt-0.5 text-xs text-sf-muted">{{ $review->created_at?->format('M j, Y') }}</p>
                @endif
            </div>
        </div>
        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-xs font-semibold text-amber-300" role="img" aria-label="Rated {{ $review->rating }} out of 5 stars">
            <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="m10 1.8 2.45 4.97 5.48.8-3.96 3.86.94 5.46L10 14.31l-4.91 2.58.94-5.46-3.96-3.86 5.48-.8L10 1.8Z"/></svg>
            {{ $review->rating }}/5
        </span>
    </header>

    @if ($showAuthor)
        <p class="mt-2 text-xs text-sf-muted">
            <a href="{{ route('films.show', $review->film) }}" class="hover:text-sf-blue hover:underline">{{ $review->film->title }}</a>
            @if (! $showDate)<span class="mx-1.5">·</span>{{ $review->created_at?->format('M j, Y') }}@endif
        </p>
    @endif

    @if ($review->comment)
        <p class="mt-4 whitespace-pre-line break-words text-sm leading-6 text-gray-300">{{ $review->comment }}</p>
    @else
        <p class="mt-4 text-sm italic text-sf-muted">No written comment was added.</p>
    @endif

    @auth
        <div class="mt-4 flex items-center gap-2 border-t border-sf-border pt-3 text-sm">
            <button type="button" class="reaction group inline-flex items-center gap-2 rounded-lg px-3 py-2 font-medium text-sf-muted transition hover:bg-sf-blue/10 hover:text-sf-blue focus:outline-none focus:ring-2 focus:ring-sf-blue/50" data-review-id="{{ $review->id }}" data-reaction="agree" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Agree with this review">
                <svg data-reaction-icon class="h-4 w-4 transition-transform duration-200 group-hover:-translate-y-0.5 group-active:scale-90" aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M7 10v10H4a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2h3Zm0 10h9.2a3 3 0 0 0 2.9-2.25l1.5-6A3 3 0 0 0 17.7 8H14l.5-3.1A2.5 2.5 0 0 0 12 2L7 10v10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="reaction-count reaction-agree-{{ $review->id }} tabular-nums">{{ $review->agree_count }}</span>
                <span class="sr-only">Agrees</span>
            </button>
            <button type="button" class="reaction group inline-flex items-center gap-2 rounded-lg px-3 py-2 font-medium text-sf-muted transition hover:bg-rose-500/10 hover:text-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400/50" data-review-id="{{ $review->id }}" data-reaction="disagree" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Disagree with this review">
                <svg data-reaction-icon class="h-4 w-4 transition-transform duration-200 group-hover:translate-y-0.5 group-active:scale-90" aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M17 14V4h3a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-3Zm0-10H7.8a3 3 0 0 0-2.9 2.25l-1.5 6A3 3 0 0 0 6.3 16H10l-.5 3.1A2.5 2.5 0 0 0 12 22l5-8V4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="reaction-count reaction-disagree-{{ $review->id }} tabular-nums">{{ $review->disagree_count }}</span>
                <span class="sr-only">Disagrees</span>
            </button>
        </div>
    @endauth

    <section class="mt-4 border-t border-sf-border pt-4" aria-label="Comments on this review">
        <h3 class="text-sm font-semibold text-white">Discussion <span class="font-normal text-sf-muted">({{ $review->comments->count() }})</span></h3>

        @if ($review->comments->isNotEmpty())
            <div class="mt-3 space-y-3">
                @foreach ($review->comments as $reviewComment)
                    <div class="flex items-start gap-2.5">
                        <x-user-avatar :user="$reviewComment->user" size="h-8 w-8" text-size="text-xs" />
                        <div class="min-w-0 flex-1 rounded-xl bg-sf-bg/70 px-3 py-2.5">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                <span class="text-xs font-semibold text-white">{{ $reviewComment->user->name }}</span>
                                <time class="text-[11px] text-sf-muted" datetime="{{ $reviewComment->created_at?->toIso8601String() }}">{{ $reviewComment->created_at?->diffForHumans() }}</time>
                            </div>
                            <p class="mt-1 whitespace-pre-line break-words text-sm leading-5 text-gray-300">{{ $reviewComment->body }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @auth
            <form action="{{ route('reviews.comments.store', $review) }}" method="POST" class="mt-3 flex items-start gap-2.5">
                @csrf
                <label class="sr-only" for="review-comment-{{ $review->id }}">Write a comment</label>
                <textarea id="review-comment-{{ $review->id }}" name="body" rows="2" maxlength="2000" required placeholder="Add to the discussion…" class="min-w-0 flex-1 resize-y rounded-xl border border-sf-border bg-sf-bg px-3 py-2.5 text-sm text-white placeholder:text-sf-muted focus:border-sf-blue focus:ring-sf-blue"></textarea>
                <button type="submit" class="shrink-0 rounded-xl bg-sf-blue px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-2 focus:ring-sf-blue/50">Comment</button>
            </form>
        @else
            <p class="mt-3 text-xs text-sf-muted"><a href="{{ route('login') }}" class="font-semibold text-sf-blue hover:underline">Log in</a> to join the discussion.</p>
        @endauth
    </section>
</article>
