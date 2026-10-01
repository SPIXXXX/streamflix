@props([
    'user',
    'size' => 'h-8 w-8',
    'textSize' => 'text-sm',
    'fallbackOnError' => false,
])

@if ($user?->avatar_url)
    @if ($fallbackOnError)
        <span class="relative inline-flex shrink-0">
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
    <span class="{{ $size }} {{ $textSize }} inline-flex shrink-0 items-center justify-center rounded-full bg-brand font-semibold text-white">
        {{ strtoupper(substr($user?->name ?? 'G', 0, 1)) }}
    </span>
@endif
