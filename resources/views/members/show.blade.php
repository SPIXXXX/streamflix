<x-app-layout>
    @php($activeTab = request('tab', 'overview'))

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('members.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-sf-muted transition hover:text-white">
            <span aria-hidden="true">←</span> Back to members
        </a>

        <header class="mt-5 overflow-hidden rounded-3xl border border-sf-border bg-gradient-to-br from-sf-surface via-sf-surface to-sf-bg p-6 shadow-xl sm:p-9">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                <x-user-avatar :user="$user" size="h-24 w-24" text-size="text-3xl" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-white">{{ $user->name }}</h1>
                        @if ($user->is_featured)
                            <span class="rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-semibold text-amber-300">Featured member</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-sf-muted">Member since {{ $user->created_at?->format('F Y') }}</p>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-sf-muted">Explore {{ $user->name }}’s reviews and public movie lists.</p>
                </div>
            </div>

            <dl class="mt-7 grid grid-cols-2 gap-3 border-t border-sf-border pt-6 sm:grid-cols-4">
                <div class="rounded-xl bg-sf-bg/70 p-4">
                    <dt class="text-xs font-medium text-sf-muted">Reviews</dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ $user->reviews_count }}</dd>
                </div>
                <div class="rounded-xl bg-sf-bg/70 p-4">
                    <dt class="text-xs font-medium text-sf-muted">Public lists</dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ $user->public_lists_count }}</dd>
                </div>
                <div class="rounded-xl bg-sf-bg/70 p-4">
                    <dt class="text-xs font-medium text-sf-muted">Average rating</dt>
                    <dd class="mt-1 text-2xl font-bold text-amber-300">{{ $user->reviews_count ? number_format((float) $user->reviews_avg_rating, 1).' ★' : '—' }}</dd>
                </div>
                <div class="rounded-xl bg-sf-bg/70 p-4">
                    <dt class="text-xs font-medium text-sf-muted">Review reactions</dt>
                    <dd class="mt-1 text-2xl font-bold text-white">{{ $user->received_reactions_count }}</dd>
                </div>
            </dl>
        </header>

        <div class="mt-8">
            <ul class="flex flex-wrap border-b border-sf-border text-center text-sm font-medium" id="member-tabs" data-tabs-toggle="#member-tab-content" data-tabs-active-classes="text-white border-sf-blue" data-tabs-inactive-classes="text-sf-muted border-transparent hover:text-white hover:border-sf-border" role="tablist">
                @foreach (['overview' => 'Overview', 'reviews' => 'Reviews', 'lists' => 'Public Lists'] as $tab => $label)
                    <li class="mr-2" role="presentation">
                        <button class="inline-block rounded-t-lg border-b-2 px-4 py-3 transition" id="{{ $tab }}-tab" data-tabs-target="#{{ $tab }}-panel" type="button" role="tab" aria-controls="{{ $tab }}-panel" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}">{{ $label }}</button>
                    </li>
                @endforeach
            </ul>

            <div id="member-tab-content" class="pt-6">
                <section class="{{ $activeTab === 'overview' ? '' : 'hidden' }}" id="overview-panel" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="grid gap-5 lg:grid-cols-2">
                        <article class="rounded-2xl border border-sf-border bg-sf-surface p-6">
                            <h2 class="text-lg font-semibold text-white">About this member</h2>
                            <p class="mt-3 text-sm leading-6 text-sf-muted">Member since {{ $user->created_at?->format('F j, Y') }}. Their public activity includes {{ $user->reviews_count }} {{ $user->reviews_count === 1 ? 'review' : 'reviews' }} and {{ $user->public_lists_count }} {{ $user->public_lists_count === 1 ? 'public list' : 'public lists' }}.</p>
                        </article>
                        <article class="rounded-2xl border border-sf-border bg-sf-surface p-6">
                            <h2 class="text-lg font-semibold text-white">Latest reviews</h2>
                            @forelse ($user->reviews()->with('film')->latest()->limit(3)->get() as $review)
                                <div class="border-b border-sf-border py-3 last:border-0">
                                    <a href="{{ route('films.show', $review->film) }}" class="font-medium text-white hover:text-sf-text">{{ $review->film->title }}</a>
                                    <span class="ml-2 text-sm text-amber-300">★ {{ $review->rating }}</span>
                                    @if ($review->comment)
                                        <p class="mt-1 line-clamp-2 text-sm text-sf-muted">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="mt-3 text-sm text-sf-muted">No reviews yet.</p>
                            @endforelse
                            @if ($user->reviews_count)
                                <a href="{{ route('members.show', ['user' => $user, 'tab' => 'reviews']) }}#member-tabs" class="mt-3 inline-block text-sm font-semibold text-sf-text hover:text-white">Browse all reviews →</a>
                            @endif
                        </article>
                    </div>

                    <div class="mt-6">
                        <div class="mb-4 flex items-end justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-white">Public movie lists</h2>
                                <p class="mt-1 text-sm text-sf-muted">Lists this member has chosen to share.</p>
                            </div>
                            @if ($user->public_lists_count)
                                <a href="{{ route('members.show', ['user' => $user, 'tab' => 'lists']) }}#member-tabs" class="shrink-0 text-sm font-semibold text-sf-text hover:text-white">View all →</a>
                            @endif
                        </div>
                        @php($overviewLists = $user->movieLists()->where('is_public', true)->withCount('films')->with('films')->latest()->limit(3)->get())
                        @if ($overviewLists->isEmpty())
                            <p class="rounded-2xl border border-sf-border bg-sf-surface px-5 py-8 text-center text-sm text-sf-muted">No public lists yet.</p>
                        @else
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($overviewLists as $list)
                                    <x-list-card :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" visibility="Public" :owner="$user" :films="$list->films" :official="$list->is_official" :category="$list->is_official ? 'Official' : 'Community'" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="{{ $activeTab === 'reviews' ? '' : 'hidden' }}" id="reviews-panel" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="rounded-2xl border border-sf-border bg-sf-surface px-5">
                        @forelse ($reviews as $review)
                            <x-review-card :review="$review" :show-author="false" :show-date="true" />
                        @empty
                            <p class="py-10 text-center text-sm text-sf-muted">No reviews yet.</p>
                        @endforelse
                    </div>
                    @if ($reviews->hasPages())
                        <div class="mt-6">{{ $reviews->links() }}</div>
                    @endif
                </section>

                <section class="{{ $activeTab === 'lists' ? '' : 'hidden' }}" id="lists-panel" role="tabpanel" aria-labelledby="lists-tab">
                    @if ($publicLists->isEmpty())
                        <p class="rounded-2xl border border-sf-border bg-sf-surface px-5 py-10 text-center text-sm text-sf-muted">No public lists yet.</p>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($publicLists as $list)
                                <x-list-card :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" visibility="Public" :owner="$user" :films="$list->films" :official="$list->is_official" :category="$list->is_official ? 'Official' : 'Community'" />
                            @endforeach
                        </div>
                        @if ($publicLists->hasPages())
                            <div class="mt-6">{{ $publicLists->links() }}</div>
                        @endif
                    @endif
                </section>
            </div>
        </div>
    </main>
</x-app-layout>
