@extends('admin.layout')

@section('title', 'Accounts')

@section('content')
    <header class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sf-text">Community management</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-white">Accounts</h1>
            <p class="mt-2 text-sm text-sf-muted">Manage members, review activity, and moderate accounts.</p>
        </div>
        <p class="text-sm text-sf-muted">{{ number_format($users->total()) }} matching {{ $users->total() === 1 ? 'account' : 'accounts' }}</p>
    </header>

    <section class="mb-7 grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Account statistics">
        @foreach ([['Total Members', $stats['total'], 'All client accounts'], ['Active Members', $stats['active'], 'Can sign in'], ['Suspended Members', $stats['suspended'], 'Temporarily restricted'], ['Recently Joined', $stats['recent'], 'Joined in the last 30 days']] as [$label, $value, $hint])
            <article class="rounded-2xl border border-sf-border bg-sf-surface p-4 shadow-lg shadow-black/10 sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-sf-muted">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-white sm:text-3xl">{{ number_format($value) }}</p>
                <p class="mt-1 text-xs text-sf-muted">{{ $hint }}</p>
            </article>
        @endforeach
    </section>

    <section class="mb-5 rounded-2xl border border-sf-border bg-sf-surface p-4 sm:p-5" aria-label="Search and filter accounts">
        <form action="{{ route('admin.accounts.index') }}" method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(14rem,1fr)_12rem_12rem_auto_auto] lg:items-end">
            <div>
                <label for="account-search" class="mb-1.5 block text-xs font-medium text-sf-muted">Search accounts</label>
                <input id="account-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email" class="block w-full rounded-xl border border-sf-border bg-sf-bg px-4 py-2.5 text-sm text-white placeholder:text-sf-muted focus:border-sf-blue focus:ring-sf-blue">
            </div>
            <div>
                <label for="account-status" class="mb-1.5 block text-xs font-medium text-sf-muted">Status</label>
                <select id="account-status" name="status" class="block w-full rounded-xl border border-sf-border bg-sf-bg px-3 py-2.5 text-sm text-white focus:border-sf-blue focus:ring-sf-blue">
                    <option value="">All statuses</option>
                    @foreach (['active' => 'Active', 'suspended' => 'Suspended', 'banned' => 'Banned'] as $status => $label)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="account-role" class="mb-1.5 block text-xs font-medium text-sf-muted">Role</label>
                <select id="account-role" name="role" class="block w-full rounded-xl border border-sf-border bg-sf-bg px-3 py-2.5 text-sm text-white focus:border-sf-blue focus:ring-sf-blue">
                    <option value="">All roles</option>
                    <option value="client" @selected(($filters['role'] ?? '') === 'client')>Client</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                </select>
            </div>
            <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-sf-blue px-5 text-sm font-semibold text-white transition hover:bg-sf-blue-dark focus:outline-none focus:ring-4 focus:ring-sf-blue/30">
                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="m21 21-4.3-4.3M19 10.5a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0Z"/></svg>
                Apply filters
            </button>
            @if (count(array_filter($filters)) > 0)
                <a href="{{ route('admin.accounts.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-sf-border px-4 text-sm font-medium text-sf-muted transition hover:bg-sf-surface-light hover:text-white">Clear</a>
            @endif
        </form>
    </section>

    @if ($users->isEmpty())
        <div class="rounded-2xl border border-sf-border bg-sf-surface px-5 py-14 text-center">
            <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-sf-bg text-sf-muted" aria-hidden="true">◎</span>
            <h2 class="mt-4 text-lg font-semibold text-white">No accounts found</h2>
            <p class="mt-1 text-sm text-sf-muted">Try changing the search term or filters.</p>
        </div>
    @else
        <div class="hidden rounded-2xl border border-sf-border bg-sf-surface md:block">
            <table class="w-full table-fixed text-left text-sm text-sf-muted">
                    <thead class="bg-sf-bg/70 text-[11px] uppercase tracking-wider text-sf-muted">
                        <tr>
                            <th scope="col" class="w-[30%] px-5 py-4">Member</th>
                            <th scope="col" class="w-[22%] px-3 py-4">Status / Role</th>
                            <th scope="col" class="w-[20%] px-3 py-4">Reviews / Rating</th>
                            <th scope="col" class="w-[11%] px-3 py-4">Lists</th>
                            <th scope="col" class="w-[12%] px-3 py-4">Joined</th>
                            <th scope="col" class="w-[5%] px-2 py-4"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sf-border">
                        @foreach ($users as $user)
                            <tr class="transition hover:bg-sf-surface-light/60">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.accounts.show', $user) }}" class="group inline-flex min-w-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-sf-blue/50">
                                        <x-user-avatar :user="$user" size="h-10 w-10" text-size="text-sm" :online="$user->is_online" :featured="$user->is_featured" />
                                        <span class="min-w-0">
                                            <span class="flex max-w-64 min-w-0 items-center gap-1.5">
                                                <span class="truncate font-semibold text-white group-hover:text-sf-text">{{ $user->name }}</span>
                                                @if ($user->is_featured)
                                                    <span class="shrink-0 rounded-full border border-amber-300/20 bg-amber-300/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-amber-200">Featured</span>
                                                @endif
                                            </span>
                                            <span class="block max-w-64 truncate text-xs text-sf-muted">{{ $user->email }}</span>
                                            <x-user-activity-status :user="$user" />
                                        </span>
                                    </a>
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <x-admin.account-status-badge :status="$user->status" />
                                        <x-admin.account-role-badge :admin="$user->hasRole('admin')" />
                                    </div>
                                </td>
                                <td class="px-3 py-4">
                                    <span class="font-semibold text-white">{{ $user->reviews_count }}</span>
                                    <span class="text-xs text-sf-muted">reviews & ratings</span>
                                    @if ($user->reviews_count)
                                        <span class="ml-1 whitespace-nowrap text-xs text-amber-300">★ {{ number_format((float) $user->reviews_avg_rating, 1) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4"><span class="font-semibold text-white">{{ $user->public_lists_count }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4">{{ $user->created_at?->format('M j, Y') }}</td>
                                <td class="px-2 py-4"><x-admin.account-actions :user="$user" context="desktop" /></td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
        </div>

        <div class="grid gap-3 md:hidden">
            @foreach ($users as $user)
                <article class="rounded-2xl border border-sf-border bg-sf-surface p-4 shadow-lg shadow-black/10">
                    <div class="flex items-start gap-3">
                        <a href="{{ route('admin.accounts.show', $user) }}" aria-label="View {{ $user->name }}'s account">
                            <x-user-avatar :user="$user" size="h-12 w-12" text-size="text-base" :online="$user->is_online" :featured="$user->is_featured" />
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('admin.accounts.show', $user) }}" class="flex min-w-0 items-center gap-1.5 font-semibold text-white hover:text-sf-text">
                                <span class="truncate">{{ $user->name }}</span>
                                @if ($user->is_featured)
                                    <span class="shrink-0 rounded-full border border-amber-300/20 bg-amber-300/10 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-amber-200">Featured</span>
                                @endif
                            </a>
                            <p class="truncate text-xs text-sf-muted">{{ $user->email }}</p>
                            <x-user-activity-status :user="$user" />
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <x-admin.account-status-badge :status="$user->status" />
                                <x-admin.account-role-badge :admin="$user->hasRole('admin')" />
                            </div>
                        </div>
                        <x-admin.account-actions :user="$user" context="mobile" />
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-sf-border pt-3 text-xs">
                        <div><dt class="text-sf-muted">Reviews / ratings</dt><dd class="mt-1 font-semibold text-white">{{ $user->reviews_count }} / {{ $user->reviews_count }}</dd></div>
                        <div><dt class="text-sf-muted">Average rating</dt><dd class="mt-1 font-semibold text-amber-300">{{ $user->reviews_count ? number_format((float) $user->reviews_avg_rating, 1).' ★' : '—' }}</dd></div>
                        <div><dt class="text-sf-muted">Public lists</dt><dd class="mt-1 font-semibold text-white">{{ $user->public_lists_count }}</dd></div>
                        <div><dt class="text-sf-muted">Joined</dt><dd class="mt-1 font-semibold text-white">{{ $user->created_at?->format('M Y') }}</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>

        @if ($users->hasPages())
            <div class="mt-6">{{ $users->links() }}</div>
        @endif
    @endif
@endsection
