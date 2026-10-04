@props(['member'])

<article class="group flex h-full flex-col rounded-2xl border border-sf-border bg-sf-surface p-5 shadow-lg shadow-black/10 transition duration-200 hover:-translate-y-1 hover:border-sf-blue/50 hover:bg-sf-surface-light hover:shadow-glow-blue focus-within:ring-2 focus-within:ring-sf-blue/50">
    <div class="flex items-start gap-4">
        <a href="{{ route('members.show', $member) }}" aria-label="View {{ $member->name }}'s profile" class="shrink-0 rounded-full focus:outline-none focus:ring-2 focus:ring-sf-blue focus:ring-offset-2 focus:ring-offset-sf-surface">
            <x-user-avatar :user="$member" size="h-14 w-14" text-size="text-xl" />
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="truncate text-base font-semibold text-white transition group-hover:text-sf-text">
                    <a href="{{ route('members.show', $member) }}" class="rounded-sm focus:outline-none focus:ring-2 focus:ring-sf-blue">{{ $member->name }}</a>
                </h3>
                @if ($member->is_featured)
                    <span class="inline-flex items-center rounded-full border border-amber-400/20 bg-amber-400/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300">Featured</span>
                @endif
            </div>
            <p class="mt-0.5 text-xs text-sf-muted">Member since {{ $member->created_at?->format('M Y') }}</p>
        </div>
    </div>

    <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-sf-border pt-4 text-xs text-sf-muted">
        <span><strong class="font-semibold text-sf-text">{{ $member->reviews_count }}</strong> {{ $member->reviews_count === 1 ? 'review' : 'reviews' }}</span>
        <span><strong class="font-semibold text-sf-text">{{ $member->public_lists_count }}</strong> {{ $member->public_lists_count === 1 ? 'public list' : 'public lists' }}</span>
        @if ($member->reviews_count > 0)
            <span class="font-semibold text-amber-300">★ {{ number_format((float) $member->reviews_avg_rating, 1) }} avg</span>
        @endif
    </div>

    <a href="{{ route('members.show', $member) }}" class="mt-5 inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-sf-blue/30 bg-sf-blue/10 px-4 text-sm font-semibold text-sf-text transition hover:border-sf-blue/60 hover:bg-sf-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">
        View Profile
        <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"/></svg>
    </a>
</article>
