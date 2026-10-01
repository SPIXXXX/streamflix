@props(['status'])

@php($classes = match ($status) {
    'active' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
    'suspended' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
    'banned' => 'border-red-400/20 bg-red-400/10 text-red-300',
    default => 'border-sf-border bg-sf-bg text-sf-muted',
})

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $classes }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ ucfirst($status) }}
</span>
