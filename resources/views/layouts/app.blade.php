<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
            <meta name="heartbeat-url" content="{{ route('heartbeat') }}">
            <meta name="user-activity-status-url" content="{{ route('user-activity.status') }}">
        @endauth

        <title>{{ config('app.name', 'CINEVAULT') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-sf-bg text-sf-text min-h-screen">
        <div class="min-h-screen pt-16">
            <x-client-navbar />

            @isset($header)
                <header class="bg-sf-surface/60 backdrop-blur border-b border-sf-border">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
        <x-confirmation-dialog />
        <x-toast-notifications />
    </body>
</html>
