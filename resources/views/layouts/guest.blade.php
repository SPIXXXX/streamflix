<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Streamflix') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-sf-text antialiased bg-sf-bg min-h-screen flex flex-col items-center justify-start sm:justify-center relative overflow-x-hidden overflow-y-auto px-4 py-8 sm:py-12">
    <div class="fixed inset-0 pointer-events-none bg-[radial-gradient(circle_at_50%_20%,rgba(59,92,252,0.15),transparent_60%)]"></div>

    <div class="relative z-10 flex flex-col items-center gap-2 mb-6">
        <div class="w-12 h-12 rounded-xl bg-sf-blue flex items-center justify-center shadow-glow-blue-lg">
            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
        </div>
        <span class="text-xl font-bold text-white">Stream<span class="text-sf-blue">flix</span></span>
    </div>

    <div class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-sf-surface/80 backdrop-blur border border-sf-border rounded-2xl shadow-glow-blue">
        {{ $slot }}
    </div>
    <x-confirmation-dialog />
    <x-toast-notifications />
</body>
</html>
