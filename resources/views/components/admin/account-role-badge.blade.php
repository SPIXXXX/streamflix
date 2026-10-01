@props(['admin' => false])

<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $admin ? 'border-violet-400/20 bg-violet-400/10 text-violet-300' : 'border-sky-400/20 bg-sky-400/10 text-sky-300' }}">
    {{ $admin ? 'Admin' : 'Client' }}
</span>
