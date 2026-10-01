@props([
    'film',
    'containerClass' => 'h-full w-full',
    'absolute' => false,
    'alt' => null,
    'loading' => 'lazy',
    'placeholderText' => 'No Poster',
])

@php($posterUrl = $film->posterUrl())

<div @class([$containerClass, 'absolute inset-0' => $absolute, 'relative' => ! $absolute, 'overflow-hidden bg-gradient-to-br from-slate-900 via-sf-surface to-slate-950'])>
    @if ($posterUrl)
        <img src="{{ $posterUrl }}" alt="{{ $alt ?? ($film->title.' poster') }}" loading="{{ $loading }}" class="block h-full w-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex')">
        <div class="absolute inset-0 hidden items-center justify-center p-3 text-center text-xs font-medium text-gray-400">{{ $placeholderText }}</div>
    @else
        <div class="absolute inset-0 flex items-center justify-center p-3 text-center text-xs font-medium text-gray-400">{{ $placeholderText }}</div>
    @endif
</div>
