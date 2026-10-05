@props([
    'user',
    'size' => 'h-8 w-8',
    'textSize' => 'text-sm',
    'fallbackOnError' => false,
    'online' => false,
    'featured' => false,
])

<span class="relative inline-flex shrink-0">
@if ($user?->avatar_url)
    @if ($fallbackOnError)
        <img
            src="{{ $user->avatar_url }}"
            alt="{{ $user->name }}'s profile picture"
            referrerpolicy="no-referrer"
            class="{{ $size }} rounded-full object-cover ring-1 ring-white/10"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('inline-flex')"
        >
        <span class="{{ $size }} {{ $textSize }} hidden items-center justify-center rounded-full bg-brand font-semibold text-white">
            {{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}
        </span>
    @else
        <img
            src="{{ $user->avatar_url }}"
            alt="{{ $user->name }}'s profile picture"
            referrerpolicy="no-referrer"
            class="{{ $size }} shrink-0 rounded-full object-cover ring-1 ring-white/10"
        >
    @endif
@else
    <span class="{{ $size }} {{ $textSize }} inline-flex items-center justify-center rounded-full bg-brand font-semibold text-white">
        {{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}
    </span>
@endif
    <span data-user-online-indicator data-user-id="{{ $user?->id }}" @class([
        'absolute bottom-0 right-0 z-10 h-3.5 w-3.5 rounded-full border-2 border-sf-surface bg-emerald-400',
        'hidden' => ! $online,
    ]) title="Online" aria-label="Online"></span>
    @if ($featured)
        <span class="absolute -right-1 -top-1 z-10 inline-flex h-4 w-4 items-center justify-center rounded-full border-2 border-sf-surface bg-amber-300 text-slate-950" title="Featured account" aria-label="Featured account">
            <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.7 2.55 5.18 5.72.83-4.14 4.04.98 5.7L10 14.76l-5.11 2.69.98-5.7L1.73 7.7l5.72-.83L10 1.7Z" /></svg>
        </span>
    @endif
</span>
