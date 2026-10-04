@props([
    'review',
    'showAuthor' => true,
    'showDate' => false,
])

<article id="review-{{ $review->id }}" x-data="{ commentsOpen: @js((int) session('open_review_comments') === $review->id) }" class="mb-4 rounded-xl border border-sf-border bg-sf-surface/70 p-4 shadow-sm last:mb-0 sm:p-5">
    <header class="flex items-start gap-3">
        @if ($showAuthor)
            <x-user-avatar :user="$review->user" size="h-10 w-10" text-size="text-sm" />
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                @if ($showAuthor)
                    <a href="{{ route('members.show', $review->user) }}" class="truncate text-sm font-semibold text-white hover:text-sf-blue">{{ $review->user->name }}</a>
                @else
                    <a href="{{ route('films.show', $review->film) }}" class="truncate text-sm font-semibold text-white hover:text-sf-blue">{{ $review->film->title }}</a>
                @endif
                <time class="text-xs text-sf-muted" datetime="{{ $review->created_at?->toIso8601String() }}">{{ $review->created_at?->diffForHumans() }}</time>
            </div>
            @if ($showAuthor)
                <p class="mt-0.5 text-xs text-sf-muted"><a href="{{ route('films.show', $review->film) }}" class="hover:text-sf-blue">{{ $review->film->title }}</a></p>
            @endif
        </div>
    </header>

    <div class="ml-0 mt-3 sm:ml-[3.25rem]">
        <div class="flex items-center gap-1" role="img" aria-label="Rated {{ $review->rating }} out of 5 stars">
            @for ($star = 1; $star <= 5; $star++)
                <span class="text-base {{ $star <= $review->rating ? 'text-amber-400' : 'text-sf-muted/50' }}" aria-hidden="true">★</span>
            @endfor
            <span class="ml-1 text-xs text-sf-muted">{{ $review->rating }}/5</span>
        </div>

        @if ($review->comment)
            <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-gray-300">{{ $review->comment }}</p>
        @else
            <p class="mt-2 text-sm italic text-sf-muted">No written comment was added.</p>
        @endif

        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            @auth
                <button type="button" class="reaction inline-flex items-center gap-1.5 rounded-md border border-sf-border bg-sf-bg/70 px-2.5 py-1.5 font-medium text-sf-muted transition hover:bg-emerald-500/10 hover:text-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400/50 {{ $review->reactions->first()?->reaction === 'agree' ? 'bg-emerald-500/10 text-emerald-400' : '' }}" data-review-id="{{ $review->id }}" data-reaction="agree" data-active="{{ $review->reactions->first()?->reaction === 'agree' ? 'true' : 'false' }}" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Like this review">
                    <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M7.5 8.5 10.6 2c.8.2 1.4 1 1.4 1.9v3.6h3.2a2 2 0 0 1 2 2.3l-.8 6a2 2 0 0 1-2 1.7H7.5V8.5ZM2 8.5h3.5v9H2v-9Z"/></svg><span class="reaction-count reaction-agree-{{ $review->id }} tabular-nums">{{ $review->agree_count }}</span>
                </button>
                <button type="button" class="reaction inline-flex items-center gap-1.5 rounded-md border border-sf-border bg-sf-bg/70 px-2.5 py-1.5 font-medium text-sf-muted transition hover:bg-rose-500/10 hover:text-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/50 {{ $review->reactions->first()?->reaction === 'disagree' ? 'bg-rose-500/10 text-rose-400' : '' }}" data-review-id="{{ $review->id }}" data-reaction="disagree" data-active="{{ $review->reactions->first()?->reaction === 'disagree' ? 'true' : 'false' }}" data-reaction-url="{{ route('reviews.reactions.store', $review) }}" aria-label="Dislike this review">
                    <svg class="h-3.5 w-3.5 rotate-180" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M7.5 8.5 10.6 2c.8.2 1.4 1 1.4 1.9v3.6h3.2a2 2 0 0 1 2 2l-.8 6a2 2 0 0 1-2 1.8H7.5V8.5ZM2 8.5h3.5v9H2v-9Z"/></svg><span class="reaction-count reaction-disagree-{{ $review->id }} tabular-nums">{{ $review->disagree_count }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-md border border-sf-border bg-sf-bg/70 px-2.5 py-1.5 font-medium text-sf-muted hover:text-emerald-400" aria-label="Log in to react to this review"><svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M7.5 8.5 10.6 2c.8.2 1.4 1 1.4 1.9v3.6h3.2a2 2 0 0 1 2 2l-.8 6a2 2 0 0 1-2 1.8H7.5V8.5ZM2 8.5h3.5v9H2v-9Z"/></svg>{{ $review->agree_count }}</a>
            @endauth
            <button type="button" @click="commentsOpen = !commentsOpen" :aria-expanded="commentsOpen.toString()" aria-controls="review-comments-{{ $review->id }}" class="inline-flex items-center gap-1.5 rounded-md border border-sf-border bg-sf-bg/70 px-2.5 py-1.5 font-medium text-sf-blue transition hover:bg-sf-blue/10">
                <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 20 20" fill="none"><path d="M3 4.75A2.75 2.75 0 0 1 5.75 2h8.5A2.75 2.75 0 0 1 17 4.75v5.5A2.75 2.75 0 0 1 14.25 13H9l-4.5 4v-4.25A2.75 2.75 0 0 1 3 10.5v-5.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                <span x-text="commentsOpen ? 'Hide comments' : 'View comments'">View comments</span>
                <span class="tabular-nums">({{ $review->comments->count() }})</span>
            </button>
        </div>

        <section id="review-comments-{{ $review->id }}" x-cloak x-show="commentsOpen" class="mt-3 border-t border-sf-border pt-3" aria-label="Comments on this review">
            @if ($review->comments->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($review->comments as $reviewComment)
                        <div x-data="{ menuOpen: false, editing: false }" class="flex items-start gap-2.5">
                            <x-user-avatar :user="$reviewComment->user" size="h-8 w-8" text-size="text-xs" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        <span class="text-xs font-semibold text-gray-200">{{ $reviewComment->user->name }}</span>
                                        <time class="text-[11px] text-sf-muted" datetime="{{ $reviewComment->created_at?->toIso8601String() }}">{{ $reviewComment->created_at?->diffForHumans() }}</time>
                                    </div>
                                    @if (auth()->id() === $reviewComment->user_id)
                                        <div class="relative shrink-0">
                                            <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-label="Comment options" class="rounded-md p-1 text-sf-muted transition hover:bg-sf-bg hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">
                                                <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg>
                                            </button>
                                            <div x-cloak x-show="menuOpen" @click.outside="menuOpen = false" class="absolute right-0 top-full z-20 mt-1 w-32 rounded-lg border border-sf-border bg-sf-surface p-1 shadow-xl">
                                                <button type="button" @click="editing = true; menuOpen = false" class="block w-full rounded-md px-3 py-2 text-left text-xs text-gray-200 hover:bg-sf-bg hover:text-white">Edit comment</button>
                                                <form action="{{ route('reviews.comments.destroy', $reviewComment) }}" method="POST" data-confirm data-confirm-title="Delete this comment?" data-confirm-message="This comment will be permanently deleted." data-confirm-label="Delete comment">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="block w-full rounded-md px-3 py-2 text-left text-xs text-rose-400 hover:bg-rose-500/10">Delete comment</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <p x-show="!editing" class="mt-0.5 whitespace-pre-line break-words text-sm leading-5 text-gray-300">{{ $reviewComment->body }}</p>
                                <form x-cloak x-show="editing" action="{{ route('reviews.comments.update', $reviewComment) }}" method="POST" class="mt-2 space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="edit-review-comment-{{ $reviewComment->id }}">Edit your comment</label>
                                    <textarea id="edit-review-comment-{{ $reviewComment->id }}" name="body" rows="2" maxlength="2000" required class="w-full rounded-lg border border-sf-border bg-sf-bg px-3 py-2 text-sm text-white focus:border-sf-blue focus:ring-sf-blue">{{ $reviewComment->body }}</textarea>
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="rounded-md bg-sf-blue px-3 py-1.5 text-xs font-semibold text-white hover:bg-sf-blue-dark">Save</button>
                                        <button type="button" @click="editing = false" class="rounded-md px-3 py-1.5 text-xs text-sf-muted hover:bg-sf-bg hover:text-white">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @auth
                <form action="{{ route('reviews.comments.store', $review) }}" method="POST" class="mt-3 flex items-center gap-2">
                    @csrf
                    <x-user-avatar :user="auth()->user()" size="h-7 w-7" text-size="text-[10px]" />
                    <label class="sr-only" for="review-comment-{{ $review->id }}">Add a comment to this review</label>
                    <input id="review-comment-{{ $review->id }}" name="body" maxlength="2000" required placeholder="Add a comment…" class="min-w-0 flex-1 rounded-lg border border-sf-border bg-sf-bg/80 px-3 py-2 text-xs text-white placeholder:text-sf-muted focus:border-sf-blue focus:ring-sf-blue">
                    <button type="submit" class="rounded-lg bg-sf-blue p-2 text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-2 focus:ring-sf-blue/50" aria-label="Send comment">
                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path d="M2.2 2.4a.75.75 0 0 1 .82-.15l14.5 6.25a1.65 1.65 0 0 1 0 3.02l-14.5 6.25a.75.75 0 0 1-1.03-.85l1.36-5.97a.75.75 0 0 1 .58-.56l6.24-1.31-6.24-1.3a.75.75 0 0 1-.58-.57L1.99 3.1a.75.75 0 0 1 .21-.7Z"/></svg>
                    </button>
                </form>
            @else
                <p class="mt-3 text-xs text-sf-muted"><a href="{{ route('login') }}" class="font-semibold text-sf-blue hover:underline">Log in</a> to join the discussion.</p>
            @endauth
        </section>
    </div>
</article>
