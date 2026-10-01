@props(['user', 'context' => 'desktop'])

@php($menuId = 'account-actions-'.$context.'-'.$user->id)

<div class="relative">
    <button id="{{ $menuId }}-button" data-dropdown-toggle="{{ $menuId }}" data-dropdown-placement="bottom-end" type="button" aria-label="Manage account for {{ $user->name }}" aria-haspopup="true" aria-controls="{{ $menuId }}" aria-expanded="false" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-sf-border bg-sf-bg text-sf-muted transition hover:border-sf-blue/50 hover:bg-sf-surface-light hover:text-white focus:outline-none focus:ring-2 focus:ring-sf-blue/50">
        <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
    </button>
    <div id="{{ $menuId }}" class="z-50 hidden w-56 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-sf-border bg-sf-surface shadow-2xl" aria-labelledby="{{ $menuId }}-button" aria-hidden="true">
        <div class="border-b border-sf-border px-3 py-2.5">
            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
            <p class="text-[11px] text-sf-muted">{{ $user->hasRole('admin') ? 'Administrator' : 'Client account' }}</p>
        </div>
        <ul class="p-1.5 text-sm" aria-label="Account actions">
            <li><a href="{{ route('admin.accounts.show', $user) }}" class="flex min-h-9 items-center gap-2.5 rounded-lg px-2.5 py-2 text-sf-muted transition hover:bg-sf-surface-light hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="1.8" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7 8a7 7 0 0 0-14 0"/></svg>View Profile</a></li>
            <li><a href="{{ route('admin.accounts.show', ['user' => $user, 'tab' => 'activity']) }}#account-tabs" class="flex min-h-9 items-center gap-2.5 rounded-lg px-2.5 py-2 text-sf-muted transition hover:bg-sf-surface-light hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M7 15l4-4 3 3 5-6"/></svg>View Activity</a></li>
            @unless ($user->hasRole('admin'))
                <li>
                    <form action="{{ route('admin.accounts.feature', $user) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="is_featured" value="{{ $user->is_featured ? 0 : 1 }}">
                        <button type="submit" class="flex min-h-9 w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60 {{ $user->is_featured ? 'text-amber-300' : 'text-sf-muted hover:text-white' }}"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>{{ $user->is_featured ? 'Remove Featured status' : 'Feature member' }}</button>
                    </form>
                </li>
                @if ($user->status === 'active')
                    <li>
                        <form action="{{ route('admin.accounts.suspend', $user) }}" method="POST" data-confirm data-confirm-title="Suspend this account?" data-confirm-message="{{ $user->name }} will lose normal account access until an admin reactivates the account." data-confirm-label="Suspend account" data-confirm-style="primary">
                            @csrf @method('PATCH')
                            <button type="submit" class="flex min-h-9 w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-amber-300 transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M6 12h12"/></svg>Suspend account</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.accounts.ban', $user) }}" method="POST" data-confirm data-confirm-title="Ban this account?" data-confirm-message="{{ $user->name }} will be banned and unable to access the account." data-confirm-label="Ban account">
                            @csrf @method('PATCH')
                            <button type="submit" class="flex min-h-9 w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-orange-300 transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="1.8" d="M5 5l14 14M19 5 5 19"/></svg>Ban account</button>
                        </form>
                    </li>
                @else
                    <li>
                        <form action="{{ route('admin.accounts.reactivate', $user) }}" method="POST" data-confirm data-confirm-title="Restore this account?" data-confirm-message="{{ $user->name }} will regain normal account access." data-confirm-label="Restore account" data-confirm-style="primary">
                            @csrf @method('PATCH')
                            <button type="submit" class="flex min-h-9 w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-emerald-300 transition hover:bg-sf-surface-light focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sf-blue/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"/></svg>{{ $user->status === 'suspended' ? 'Unsuspend account' : 'Reactivate account' }}</button>
                        </form>
                    </li>
                @endif
                <li class="mt-1 border-t border-sf-border pt-1">
                    <form action="{{ route('admin.accounts.destroy', $user) }}" method="POST" data-confirm data-confirm-title="Delete this account?" data-confirm-message="{{ $user->name }} and the account's related content will be permanently deleted." data-confirm-label="Delete account">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex min-h-9 w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-red-300 transition hover:bg-red-500/10 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-400/60"><svg class="h-4 w-4 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>Delete account</button>
                    </form>
                </li>
            @endunless
        </ul>
    </div>
</div>
