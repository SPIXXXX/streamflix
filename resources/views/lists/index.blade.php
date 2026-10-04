<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-9 flex flex-col gap-5 rounded-3xl border border-sf-border bg-gradient-to-br from-sf-surface via-sf-surface to-sf-bg p-6 shadow-xl sm:flex-row sm:items-end sm:justify-between sm:p-8">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Movie Bucket List</h1>
            </div>
            @auth
                <a href="{{ route('lists.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-sf-blue px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sf-blue/15 transition hover:bg-sf-blue-dark focus:outline-none focus:ring-4 focus:ring-sf-blue/30">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
                    Create List
                </a>
            @endauth
        </header>

        @auth
            <section class="mb-12" aria-labelledby="my-lists-heading">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="my-lists-heading" class="text-xl font-semibold text-white">My Lists</h2>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <x-list-card
                        :list="$favoriteList"
                        title="Favorites"
                        :href="route('lists.favorites')"
                        :description="$favoriteList->description"
                        :count="$favoriteCount"
                        visibility="Private"
                        :owner="auth()->user()"
                        :films="$favoritePreviews"
                        icon="heart"
                        category="Favorites"
                        :poster-mode="true"
                    />
                    @foreach ($myLists as $list)
                        <x-list-card :list="$list" :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" :visibility="$list->is_public ? 'Public' : 'Private'" :owner="$list->user" :films="$list->films" :official="$list->is_official" :category="$list->is_official ? 'Official' : 'Personal'" :poster-mode="true" />
                    @endforeach
                </div>
                @if ($favoriteCount === 0 && $myLists->isEmpty())
                    <p class="mt-3 text-sm text-sf-muted">No movie lists yet. <a href="{{ route('films.index') }}" class="text-sf-blue hover:underline">Browse films</a> or create a list to get started.</p>
                @endif
            </section>
        @endauth

        @foreach ([['Featured Lists', $featured, 'Featured'], ['Recently Popular', $recentlyPopular, 'Popular'], ['Crew Picks', $crewPicks, 'Crew Pick']] as [$label, $lists, $category])
            <section class="mb-12" aria-label="{{ $label }}">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-white">{{ $label }}</h2>
                </div>
                @if ($lists->isNotEmpty())
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($lists as $list)
                            <x-list-card :list="$list" :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" :visibility="$list->is_public ? 'Public' : 'Private'" :owner="$list->user" :films="$list->films" :official="$list->is_official" :category="$category" :poster-mode="true" />
                        @endforeach
                    </div>
                @else
                    <p class="rounded-xl border border-sf-border bg-sf-surface/60 px-4 py-5 text-sm text-sf-muted">No lists here yet.</p>
                @endif
            </section>
        @endforeach

        <section class="mb-8" aria-labelledby="public-lists-heading">
            <div class="mb-4">
                <h2 id="public-lists-heading" class="text-xl font-semibold text-white">Explore Public Lists</h2>
            </div>
            @if ($publicLists->isNotEmpty())
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($publicLists as $list)
                        <x-list-card :list="$list" :title="$list->title" :href="route('lists.show', $list)" :description="$list->description" :count="$list->films_count" visibility="Public" :owner="$list->user" :films="$list->films" :official="$list->is_official" :category="$list->is_official ? 'Official' : 'Community'" :poster-mode="true" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $publicLists->links() }}</div>
            @else
                <p class="rounded-xl border border-sf-border bg-sf-surface/60 px-4 py-5 text-sm text-sf-muted">No public lists to explore yet.</p>
            @endif
        </section>
    </div>
</x-app-layout>
