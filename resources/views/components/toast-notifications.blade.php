@php
    $toastMessages = [];
    $status = session('status');
    $statusMessages = [
        'profile-updated' => ['success', 'Profile updated successfully.'],
        'password-updated' => ['success', 'Password updated successfully.'],
        'verification-link-sent' => ['success', 'A new verification link has been sent to your email address.'],
    ];

    foreach (['success', 'error', 'warning', 'info'] as $toastType) {
        $message = session($toastType);
        if (is_string($message) && $message !== '') {
            $toastMessages[] = ['type' => $toastType, 'message' => $message];
        }
    }

    if (is_string($status) && $status !== '' && $status !== 'google-delete-ready' && ! session('error')) {
        [$type, $message] = $statusMessages[$status] ?? ['success', $status];
        $toastMessages[] = ['type' => $type, 'message' => $message];
    }

    if ($errors->any()) {
        $toastMessages[] = ['type' => 'error', 'message' => 'Please review the highlighted fields and try again.'];
    }

    $toastMessages = collect($toastMessages)->unique(fn ($toast) => $toast['type'].'|'.$toast['message'])->values();
    $toastStyles = [
        'success' => ['border-emerald-500/25 bg-emerald-950/95 text-emerald-100', 'bg-emerald-500/15 text-emerald-300', 'Success'],
        'error' => ['border-red-500/25 bg-red-950/95 text-red-100', 'bg-red-500/15 text-red-300', 'Error'],
        'warning' => ['border-amber-500/25 bg-amber-950/95 text-amber-100', 'bg-amber-500/15 text-amber-300', 'Warning'],
        'info' => ['border-sky-500/25 bg-sky-950/95 text-sky-100', 'bg-sky-500/15 text-sky-300', 'Information'],
    ];
@endphp

<div data-toast-container class="pointer-events-none fixed inset-x-0 top-20 z-[50] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-6 sm:top-24 sm:w-full sm:max-w-sm sm:items-end sm:px-0" aria-live="polite" aria-atomic="false">
    @foreach ($toastMessages as $toast)
        @php [$containerStyle, $iconStyle, $accessibleType] = $toastStyles[$toast['type']]; @endphp
        <div data-toast role="{{ $toast['type'] === 'error' ? 'alert' : 'status' }}" class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border px-4 py-3.5 text-sm shadow-2xl shadow-black/30 backdrop-blur transition duration-200 {{ $containerStyle }}">
            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $iconStyle }}" aria-hidden="true">
                @if ($toast['type'] === 'success') ✓ @elseif ($toast['type'] === 'error') ! @elseif ($toast['type'] === 'warning') ⚠ @else i @endif
            </span>
            <p class="min-w-0 flex-1 leading-6"><span class="sr-only">{{ $accessibleType }}: </span>{{ $toast['message'] }}</p>
            <button type="button" data-toast-close aria-label="Dismiss notification" class="-me-1 -mt-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-current/70 transition hover:bg-white/10 hover:text-current focus:outline-none focus:ring-2 focus:ring-white/40">
                <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 12 12M13 1 1 13"/></svg>
            </button>
        </div>
    @endforeach
</div>
