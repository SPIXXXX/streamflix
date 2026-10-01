@extends('admin.layout')

@section('title', $user->name.' · Account')

@section('content')
    @php($activeTab = in_array(request('tab'), ['overview', 'activity', 'reviews', 'lists'], true) ? request('tab') : 'overview')

    <a href="{{ route('admin.accounts.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-sf-muted transition hover:text-white">
        <span aria-hidden="true">←</span> Back to Accounts
    </a>

    <header class="mt-5 rounded-3xl border border-sf-border bg-gradient-to-br from-sf-surface via-sf-surface to-sf-bg p-5 shadow-xl sm:p-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <x-user-avatar :user="$user" size="h-20 w-20 sm:h-24 sm:w-24" text-size="text-3xl" />
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="break-words text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $user->name }}</h1>
                    <x-admin.account-status-badge :status="$user->status" />
                    <x-admin.account-role-badge :admin="$user->hasRole('admin')" />
                    @if ($user->is_featured)
                        <span class="inline-flex rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[11px] font-semibold text-amber-300">Featured member</span>
                    @endif
                </div>
                <p class="mt-2 break-all text-sm text-sf-muted">{{ $user->email }}</p>
                <p class="mt-1 text-xs text-sf-muted">Joined {{ $user->created_at?->format('F j, Y') }}</p>
            </div>
            <x-admin.account-actions :user="$user" context="profile" />
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-3 border-t border-sf-border pt-5 sm:grid-cols-4">
            @foreach ([['Reviews / ratings', $user->reviews_count], ['Average rating', $user->reviews_count ? number_format((float) $user->reviews_avg_rating, 1).' / 5' : '—'], ['Public lists', $user->public_lists_count], ['Favorite films', $user->favorite_films_count]] as [$label, $value])
                <div class="rounded-xl bg-sf-bg/70 p-3.5 sm:p-4">
                    <dt class="text-xs font-medium text-sf-muted">{{ $label }}</dt>
                    <dd class="mt-1 text-xl font-bold text-white sm:text-2xl">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </header>

    <section class="mt-7">
        <ul class="flex max-w-full overflow-x-auto border-b border-sf-border text-center text-sm font-medium" id="account-tabs" data-tabs-toggle="#account-tab-panels" data-tabs-active-classes="border-sf-blue text-white" data-tabs-inactive-classes="border-transparent text-sf-muted hover:border-sf-border hover:text-white" role="tablist" aria-label="Account information">
            @foreach (['overview' => 'Overview', 'activity' => 'Activity', 'reviews' => 'Reviews', 'lists' => 'Public Lists'] as $tab => $label)
                <li class="mr-2 shrink-0" role="presentation">
                    <button id="account-{{ $tab }}-tab" data-tabs-target="#account-{{ $tab }}-panel" type="button" role="tab" aria-controls="account-{{ $tab }}-panel" aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}" class="inline-block whitespace-nowrap rounded-t-lg border-b-2 px-4 py-3 transition">{{ $label }}</button>
                </li>
            @endforeach
        </ul>

        <div id="account-tab-panels" class="pt-6">
            <section id="account-overview-panel" class="{{ $activeTab === 'overview' ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="account-overview-tab">
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)]">
                    <article class="rounded-2xl border border-sf-border bg-sf-surface p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-white">Account details</h2>
                        <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                            <div><dt class="text-xs font-medium text-sf-muted">Full name</dt><dd class="mt-1 text-white">{{ $user->name }}</dd></div>
                            <div><dt class="text-xs font-medium text-sf-muted">Email</dt><dd class="mt-1 break-all text-white">{{ $user->email }}</dd></div>
                            <div><dt class="text-xs font-medium text-sf-muted">Role</dt><dd class="mt-1 text-white">{{ $user->hasRole('admin') ? 'Administrator' : 'Client' }}</dd></div>
                            <div><dt class="text-xs font-medium text-sf-muted">Account status</dt><dd class="mt-1 text-white">{{ ucfirst($user->status) }}</dd></div>
                            <div><dt class="text-xs font-medium text-sf-muted">Joined</dt><dd class="mt-1 text-white">{{ $user->created_at?->format('M j, Y · g:i A') }}</dd></div>
                            <div><dt class="text-xs font-medium text-sf-muted">Reviews and ratings</dt><dd class="mt-1 text-white">{{ $user->reviews_count }} recorded</dd></div>
                        </dl>
                        <p class="mt-5 rounded-xl border border-sf-border bg-sf-bg/70 p-4 text-sm leading-6 text-sf-muted">The application does not currently store a member bio, username, login history, or moderation history.</p>
                    </article>

                    <article class="rounded-2xl border border-sf-border bg-sf-surface p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div><h2 class="text-lg font-semibold text-white">Recent activity</h2><p class="mt-1 text-xs text-sf-muted">From recorded reviews, favorites, reactions, and public lists.</p></div>
                            <a href="{{ route('admin.accounts.show', ['user' => $user, 'tab' => 'activity']) }}#account-tabs" class="shrink-0 text-sm font-semibold text-sf-blue hover:text-white">View all</a>
                        </div>
                        @forelse ($activities->getCollection()->take(5) as $activity)
                            <div class="flex gap-3 border-b border-sf-border py-3 last:border-0">
                                <span class="mt-1 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sf-blue/10 text-xs text-sf-blue" aria-hidden="true">
                                    {{ ['review' => '★', 'list' => '▤', 'favorite' => '♥', 'reaction' => '↗', 'list_add' => '+'][$activity->type] ?? '•' }}
                                </span>
                                <p class="min-w-0 flex-1 text-sm leading-5 text-sf-text">@include('admin.accounts.partials.activity-description', ['activity' => $activity])<span class="mt-1 block text-xs text-sf-muted">{{ \Illuminate\Support\Carbon::parse($activity->occurred_at)->diffForHumans() }}</span></p>
                            </div>
                        @empty
                            <p class="mt-5 rounded-xl border border-dashed border-sf-border px-4 py-6 text-center text-sm text-sf-muted">No activity recorded yet.</p>
                        @endforelse
                    </article>
                </div>
            </section>

            <section id="account-activity-panel" class="{{ $activeTab === 'activity' ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="account-activity-tab">
                @if ($activities->isEmpty())
                    <div class="rounded-2xl border border-dashed border-sf-border bg-sf-surface px-5 py-12 text-center text-sm text-sf-muted">No activity recorded yet.</div>
                @else
                    <ol class="relative ml-3 border-s border-sf-border">
                        @foreach ($activities as $activity)
                            <li class="mb-6 ms-6 last:mb-0">
                                <span class="absolute -start-3 flex h-6 w-6 items-center justify-center rounded-full border border-sf-border bg-sf-surface text-xs text-sf-blue" aria-hidden="true">{{ ['review' => '★', 'list' => '▤', 'favorite' => '♥', 'reaction' => '↗', 'list_add' => '+'][$activity->type] ?? '•' }}</span>
                                <article class="rounded-2xl border border-sf-border bg-sf-surface p-4 sm:p-5">
                                    <p class="text-sm leading-6 text-white">@include('admin.accounts.partials.activity-description', ['activity' => $activity])</p>
                                    <time class="mt-2 block text-xs text-sf-muted" datetime="{{ \Illuminate\Support\Carbon::parse($activity->occurred_at)->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($activity->occurred_at)->format('M j, Y · g:i A') }}</time>
                                </article>
                            </li>
                        @endforeach
                    </ol>
                    @if ($activities->hasPages())<div class="mt-6">{{ $activities->links() }}</div>@endif
                @endif
            </section>

            <section id="account-reviews-panel" class="{{ $activeTab === 'reviews' ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="account-reviews-tab">
                @if ($reviews->isEmpty())
                    <div class="rounded-2xl border border-dashed border-sf-border bg-sf-surface px-5 py-12 text-center text-sm text-sf-muted">No reviews or ratings yet.</div>
                @else
                    <div class="grid gap-3">
                        @foreach ($reviews as $review)
                            <article class="flex gap-4 rounded-2xl border border-sf-border bg-sf-surface p-4 sm:p-5">
                                @if ($review->film?->poster_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($review->film->poster_path) }}" alt="{{ $review->film->title }} poster" loading="lazy" class="h-24 w-16 shrink-0 rounded-lg border border-sf-border object-cover sm:h-28 sm:w-20">
                                @else
                                    <div class="flex h-24 w-16 shrink-0 items-center justify-center rounded-lg border border-sf-border bg-sf-bg text-xl text-sf-muted sm:h-28 sm:w-20" aria-hidden="true">▶</div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            @if ($review->film)
                                                <a href="{{ route('films.show', $review->film) }}" class="font-semibold text-white hover:text-sf-blue">{{ $review->film->title }}</a>
                                            @else
                                                <span class="font-semibold text-sf-muted">Film unavailable</span>
                                            @endif
                                            <p class="mt-1 text-sm text-amber-300">★ {{ $review->rating }} / 5</p>
                                        </div>
                                        <time class="text-xs text-sf-muted">{{ $review->created_at?->format('M j, Y') }}</time>
                                    </div>
                                    @if ($review->comment)<p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-sf-text">{{ $review->comment }}</p>@endif
                                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-sf-muted" aria-label="Review reactions">
                                        <span>👍 {{ $review->agree_count }} agree</span><span>👎 {{ $review->disagree_count }} disagree</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($reviews->hasPages())<div class="mt-6">{{ $reviews->links() }}</div>@endif
                @endif
            </section>

            <section id="account-lists-panel" class="{{ $activeTab === 'lists' ? '' : 'hidden' }}" role="tabpanel" aria-labelledby="account-lists-tab">
                @if ($publicLists->isEmpty())
                    <div class="rounded-2xl border border-dashed border-sf-border bg-sf-surface px-5 py-12 text-center text-sm text-sf-muted">No public lists to show.</div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($publicLists as $list)
                            <x-list-card :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" visibility="Public" :owner="$user" :films="$list->films" :official="$list->is_official" :category="$list->is_official ? 'Official' : 'Community'" />
                        @endforeach
                    </div>
                    @if ($publicLists->hasPages())<div class="mt-6">{{ $publicLists->links() }}</div>@endif
                @endif
            </section>
        </div>
    </section>
@endsection
