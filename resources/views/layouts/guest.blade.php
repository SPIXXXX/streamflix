<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CINEVAULT') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-sf-text antialiased bg-sf-bg min-h-screen flex flex-col items-center justify-start sm:justify-center relative overflow-x-hidden overflow-y-auto px-4 py-8 sm:py-12">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(circle_at_50%_20%,rgba(59,92,252,0.15),transparent_60%)]"></div>

    <div class="relative z-10 mb-6 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sf-blue text-white shadow-glow-blue-lg">
            <x-application-logo class="h-6 w-6" aria-hidden="true" />
        </div>
        <span class="text-lg font-black tracking-[0.16em] text-white">CINEVAULT</span>
    </div>

    <div class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-sf-surface/80 backdrop-blur border border-sf-border rounded-2xl shadow-glow-blue">
        {{ $slot }}
    </div>
    <x-confirmation-dialog />
    <x-toast-notifications />
</body>
</html>
