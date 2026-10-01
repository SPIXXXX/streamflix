@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="flex items-end justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-sf-surface border border-sf-border text-sf-muted">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span class="text-sm">Overview</span>
        </div>
    </div>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4 mb-4">
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Total Users</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $totalUsers }}</div>
        </div>
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Total Films</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $totalFilms }}</div>
        </div>
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Reviews This Week</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $reviewsThisWeek }}</div>
        </div>
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Active Members</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $activeMembers }}</div>
        </div>
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Reviews This Month</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $monthlyActivity->sum('total') }}</div>
            <div class="mt-1 text-xs text-sf-muted">Last 30 days</div>
        </div>
        <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
            <div class="text-xs uppercase tracking-wider text-sf-muted">Reviews This Year</div>
            <div class="mt-3 text-3xl font-extrabold">{{ $yearlyActivity->sum('total') }}</div>
            <div class="mt-1 text-xs text-sf-muted">Last 12 months</div>
        </div>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-2">
        <section class="bg-sf-surface border border-sf-border rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-lg font-semibold">Popular Genres</h2>
                    <p class="text-sm text-sf-muted">Film distribution by genre</p>
                </div>
                <span class="rounded-full bg-sf-bg px-3 py-1 text-xs text-sf-muted">Library</span>
            </div>
            <div id="genreChart" class="min-h-64" aria-label="Popular genres chart"></div>
        </section>

        <section class="bg-sf-surface border border-sf-border rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-lg font-semibold">Popular Decades</h2>
                    <p class="text-sm text-sf-muted">Films grouped by release decade</p>
                </div>
                <span class="rounded-full bg-sf-bg px-3 py-1 text-xs text-sf-muted">Catalog</span>
            </div>
            <div id="decadeChart" class="min-h-64" aria-label="Popular decades chart"></div>
        </section>

        <section class="bg-sf-surface border border-sf-border rounded-xl p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-lg font-semibold">Review Activity</h2>
                    <p class="text-sm text-sf-muted">Reviews submitted over the last 12 months</p>
                </div>
                <span class="rounded-full bg-sf-bg px-3 py-1 text-xs text-sf-muted">12 months</span>
            </div>
            <div id="activityChart" class="min-h-64" aria-label="Review activity chart"></div>
        </section>
    </div>

    <div class="bg-sf-surface border border-sf-border rounded-xl p-6 mt-4">
        <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-semibold">Recent Activity</div>
            <a href="{{ route('admin.films.index') }}" class="px-3 py-1.5 rounded-lg border border-sf-border text-sm">Open Films</a>
        </div>

        <div class="grid gap-3">
            @forelse ($recentActivity as $review)
                <div class="flex items-center justify-between gap-4 border-b border-sf-border pb-3">
                    <div class="flex items-center gap-2">
                        <x-user-avatar :user="$review->user" size="h-8 w-8" text-size="text-xs" />
                        <div>
                            <strong class="mr-1">{{ $review->user->name }}</strong>
                        <span class="text-sf-muted">reviewed</span>
                        <span class="text-sf-muted">{{ $review->film->title }}</span>
                        </div>
                    </div>
                    <div class="text-sf-muted">★ {{ $review->rating }}</div>
                </div>
            @empty
                <div class="text-sf-muted">No recent activity.</div>
            @endforelse
        </div>
    </div>

    @php
        $dashboardChartData = [
            'genres' => [
                'labels' => $popularGenres->pluck('genre')->values(),
                'values' => $popularGenres->pluck('total')->values(),
            ],
            'decades' => [
                'labels' => $popularDecades->pluck('decade')->values(),
                'values' => $popularDecades->pluck('total')->values(),
            ],
            'activity' => [
                'labels' => $yearlyActivity->pluck('month')->values(),
                'values' => $yearlyActivity->pluck('total')->values(),
            ],
        ];
    @endphp
    <script type="application/json" id="admin-dashboard-chart-data">@json($dashboardChartData)</script>
@endsection
