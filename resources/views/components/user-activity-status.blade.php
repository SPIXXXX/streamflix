@props(['user'])

@php($isOnline = $user->is_online)

<span data-user-activity-status data-user-id="{{ $user->id }}" @class([
    'inline-flex items-center gap-1.5 text-xs',
    'font-semibold text-emerald-300' => $isOnline,
    'text-sf-muted' => ! $isOnline,
]) role="status" aria-label="{{ $user->activity_status }}">
    <span @class([
        'activity-status-indicator',
        'h-1.5 w-1.5 shrink-0 rounded-full',
        'bg-emerald-400' => $isOnline,
        'bg-slate-500' => ! $isOnline,
    ]) aria-hidden="true"></span>
    <span data-activity-status-label>{{ $user->activity_status }}</span>
</span>
