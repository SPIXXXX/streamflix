<x-app-layout>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-10 overflow-hidden rounded-3xl border border-sf-border bg-gradient-to-br from-sf-surface via-sf-surface to-sf-bg p-6 shadow-xl sm:p-9">
            <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,24rem)] lg:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sf-blue">Find your co-reviewers</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Members</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-sf-muted sm:text-base">Discover reviewers, see what they think of the films you love, and explore their public movie lists.</p>
                </div>
                <form action="{{ route('members.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row lg:flex-col">
                    <label for="member-search" class="sr-only">Search members by name</label>
                    <input id="member-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Search members..." class="min-w-0 flex-1 rounded-xl border border-sf-border bg-sf-bg px-4 py-3 text-sm text-white placeholder:text-sf-muted focus:border-sf-blue focus:ring-sf-blue">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-sf-blue px-5 text-sm font-semibold text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-4 focus:ring-sf-blue/30">Search Members</button>
                </form>
            </div>
        </header>

        <section id="members-directory" class="mb-10 scroll-mt-24" aria-labelledby="members-directory-heading">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="members-directory-heading" class="text-xl font-semibold text-white sm:text-2xl">{{ ['active' => 'Most Active Reviewers', 'popular' => 'Popular Members', 'featured' => 'Featured Members'][$sort] ?? 'All Members' }}</h2>
                    <p class="mt-1 text-sm text-sf-muted">{{ $members->total() }} {{ $members->total() === 1 ? 'member' : 'members' }} found</p>
                </div>
                <nav class="flex max-w-full gap-2 overflow-x-auto pb-1" aria-label="Filter members">
                    @foreach (['all' => 'All', 'active' => 'Most Active', 'popular' => 'Popular', 'featured' => 'Featured'] as $filter => $label)
                        <a href="{{ route('members.index', array_filter(['sort' => $filter === 'all' ? null : $filter, 'q' => $filters['q'] ?? null])) }}#members-directory" @class([
                            'shrink-0 rounded-full border px-3.5 py-2 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-sf-blue/50',
                            'border-sf-blue bg-sf-blue/10 text-white' => $sort === $filter,
                            'border-sf-border bg-sf-surface text-sf-muted hover:border-sf-blue/50 hover:text-white' => $sort !== $filter,
                        ])>{{ $label }}</a>
                    @endforeach
                </nav>
            </div>

            @if ($members->isEmpty())
                <div class="rounded-2xl border border-sf-border bg-sf-surface/60 px-5 py-10 text-center">
                    <h3 class="font-semibold text-white">No members found</h3>
                    <p class="mt-1 text-sm text-sf-muted">Try another name or choose a different member filter.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($members as $member)
                        <x-member-card :member="$member" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $members->links() }}</div>
            @endif
        </section>

        @foreach ([['most-active', 'Most Active Reviewers', 'Members with the most published reviews.', $mostActive, 'active'], ['featured', 'Featured Members', 'Community members selected by an administrator.', $featured, 'featured'], ['popular', 'Popular Members', 'Ranked by review reactions, reviews, and public movie lists.', $popular, 'popular']] as [$id, $title, $description, $sectionMembers, $sectionSort])
            <section class="mb-10" aria-labelledby="{{ $id }}-heading">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="{{ $id }}-heading" class="text-xl font-semibold text-white sm:text-2xl">{{ $title }}</h2>
                        <p class="mt-1 text-sm text-sf-muted">{{ $description }}</p>
                    </div>
                    <a href="{{ route('members.index', ['sort' => $sectionSort]) }}#members-directory" class="shrink-0 rounded-lg px-2 py-2 text-sm font-semibold text-sf-blue transition hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">View All <span aria-hidden="true">→</span></a>
                </div>
                @if ($sectionMembers->isEmpty())
                    <div class="rounded-2xl border border-sf-border bg-sf-surface/60 px-5 py-7 text-sm text-sf-muted">
                        @if ($sectionSort === 'featured')
                            No featured members yet.
                        @elseif ($sectionSort === 'active')
                            No members have posted reviews yet.
                        @else
                            No popular members to show yet.
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($sectionMembers as $member)
                            <x-member-card :member="$member" />
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

    </main>
</x-app-layout>
