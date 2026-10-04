<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f1726">
    <meta name="description" content="Find your next favorite film, share your reviews, and build movie lists with the Cinevault community.">
    <title>{{ config('app.name', 'CINEVAULT') }} — Find your next favorite</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-[#0f1726] font-sans text-white antialiased">
    <header class="absolute inset-x-0 top-0 z-20">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-4 py-4 sm:px-8 sm:py-5 lg:px-12" aria-label="Main navigation">
            <a href="{{ route('home') }}" class="flex min-w-0 shrink items-center gap-2 sm:gap-3" aria-label="Cinevault home">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#B8001F] text-[#FCFAEE] shadow-lg shadow-red-900/20 sm:h-10 sm:w-10">
                    <x-application-logo class="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" />
                </span>
                <span class="truncate text-[13px] font-black tracking-[0.12em] sm:text-lg sm:tracking-[0.18em]">CINEVAULT</span>
            </a>

            <div class="flex shrink-0 items-center gap-2 sm:gap-6">
                <a href="{{ route('films.index') }}" class="hidden text-sm font-medium text-white/75 transition hover:text-white sm:inline">Explore films</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="whitespace-nowrap rounded-full border border-white/20 px-3 py-2 text-xs font-semibold transition hover:border-white/50 hover:bg-white/10 sm:px-5 sm:py-2.5 sm:text-sm">Go to your account</a>
                @else
                    <a href="{{ route('login') }}" class="whitespace-nowrap text-xs font-semibold text-white/80 transition hover:text-white sm:text-sm">Log in</a>
                    <a href="{{ route('register') }}" class="whitespace-nowrap rounded-full bg-[#B8001F] px-3 py-2 text-[11px] font-bold text-[#FCFAEE] transition hover:bg-[#940019] sm:px-5 sm:py-2.5 sm:text-sm">Join Cinevault</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <section class="relative isolate flex min-h-[680px] items-center overflow-hidden pt-20 sm:min-h-[720px] sm:pt-24 lg:min-h-[780px]">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(ellipse_at_76%_42%,rgba(184,0,31,.22),transparent_40%),radial-gradient(ellipse_at_17%_78%,rgba(34,54,76,.3),transparent_38%),linear-gradient(115deg,#0f1726_12%,#0f1726_56%,#1c2b40)]"></div>
            <div class="absolute right-[-12%] top-[12%] -z-10 h-[570px] w-[570px] rounded-full border border-white/[0.04] sm:right-[3%] sm:h-[680px] sm:w-[680px]"></div>
            <div class="absolute right-[-4%] top-[21%] -z-10 h-[450px] w-[450px] rounded-full border border-white/[0.05] sm:right-[11%] sm:h-[520px] sm:w-[520px]"></div>
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#0f1726] via-[#0f1726]/95 to-transparent"></div>

            <div class="mx-auto grid w-full max-w-7xl items-center gap-10 px-4 pb-16 pt-10 sm:gap-12 sm:px-8 sm:pb-20 sm:pt-12 lg:grid-cols-[1.02fr_.98fr] lg:px-12 lg:pb-28">
                <div class="max-w-2xl">
                    <div class="mb-6 inline-flex max-w-full items-center gap-2 rounded-full border border-[#B8001F]/25 bg-[#B8001F]/[0.08] px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.13em] text-[#FCFAEE] sm:mb-7 sm:px-4 sm:text-xs sm:tracking-[0.16em]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#B8001F]"></span>
                        A home for people who love film
                    </div>
                    <h1 class="max-w-2xl text-[2.55rem] font-black leading-[1.04] tracking-[-0.045em] sm:text-6xl lg:text-[4.5rem]">Every film has a story.<br><span class="text-[#FCFAEE]">Find yours.</span></h1>
                    <p class="mt-5 max-w-xl text-sm leading-6 text-white/65 sm:mt-7 sm:text-lg sm:leading-8">Discover your next favorite, keep track of what you watch, and find thoughtful recommendations from a community that loves movies as much as you do.</p>
                    <div class="mt-7 flex flex-col gap-3 sm:mt-9 sm:flex-row">
                        <a href="{{ route('films.index') }}" class="inline-flex w-full items-center justify-center gap-3 rounded-full bg-[#B8001F] px-6 py-3.5 text-sm font-extrabold text-[#FCFAEE] shadow-xl shadow-red-900/20 transition hover:-translate-y-0.5 hover:bg-[#940019] sm:w-auto sm:px-7 sm:py-4">
                            Explore the collection
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-full border border-white/20 px-6 py-3.5 text-sm font-bold text-white transition hover:border-white/45 hover:bg-white/[0.06] sm:w-auto sm:px-7 sm:py-4">Create a free account</a>
                        @endguest
                    </div>
                    <div class="mt-9 flex items-center gap-3 border-t border-white/10 pt-5 text-xs text-white/55 sm:mt-12 sm:gap-4 sm:pt-6 sm:text-sm">
                        <div class="flex -space-x-2" aria-hidden="true">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#0f1726] bg-[#1c2b40] text-[10px] font-bold text-white">J</span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#0f1726] bg-[#0f1726] text-[10px] font-bold text-white">M</span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[#0f1726] bg-[#3A5068] text-[10px] font-bold text-white">A</span>
                        </div>
                        <p><span class="font-semibold text-white">Your people are here.</span> Make every watch count.</p>
                    </div>
                </div>

                <div class="relative mx-auto hidden h-[470px] w-full max-w-[560px] items-center justify-center lg:flex" aria-hidden="true">
                    <div class="absolute right-[3%] top-[4%] h-[390px] w-[260px] rotate-[9deg] overflow-hidden rounded-2xl border border-white/10 bg-[#1c2b40] shadow-2xl shadow-black/70">
                        <img class="h-full w-full object-cover opacity-80" src="https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&amp;fit=crop&amp;w=800&amp;q=85" alt="" onerror="this.style.display='none'">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6"><span class="text-[10px] font-bold uppercase tracking-[.24em] text-[#FCFAEE]">The collection</span><p class="mt-2 text-2xl font-black">Stories<br>worth keeping.</p></div>
                    </div>
                    <div class="absolute left-[7%] top-[17%] h-[365px] w-[245px] -rotate-[10deg] overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#293D54] via-[#1c2b40] to-[#0f1726] shadow-2xl shadow-black/70">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_38%,rgba(232,183,107,.65),transparent_26%),linear-gradient(150deg,transparent_40%,rgba(4,7,8,.92))]"></div>
                        <div class="absolute left-7 top-8 h-44 w-36 rounded-[48%_48%_42%_42%] bg-gradient-to-b from-[#d6b68b]/80 to-[#0f1726]/90 blur-[1px]"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6"><span class="text-[10px] font-bold uppercase tracking-[.24em] text-white/65">Curated for you</span><p class="mt-2 text-3xl font-black leading-none">A world<br>of cinema.</p></div>
                    </div>
                    <div class="absolute bottom-[4%] right-[7%] rounded-2xl border border-white/10 bg-[#0f1726]/90 p-4 shadow-xl backdrop-blur">
                        <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#B8001F]/15 text-[#FCFAEE]"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="m10 1.5 2.37 4.8 5.3.77-3.84 3.74.91 5.28L10 13.6l-4.74 2.49.9-5.28-3.83-3.74 5.3-.77L10 1.5Z"/></svg></span><div><p class="text-xs text-white/50">Your next favorite</p><p class="mt-0.5 text-sm font-bold">is out there.</p></div></div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-28 bg-gradient-to-t from-[#0f1726] to-transparent"></div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-8 sm:pb-20 lg:px-12 lg:pb-28">
            <div class="grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-white/[0.08] bg-white/[0.025] p-5 transition hover:border-white/15 hover:bg-white/[0.04] sm:p-7">
                    <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#B8001F]/10 text-[#FCFAEE]"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.47 9.77l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg></span>
                    <h2 class="text-lg font-bold">Find something worth watching</h2><p class="mt-2 text-sm leading-6 text-white/55">Browse a growing collection and explore films by what you’re in the mood for.</p>
                </article>
                <article class="rounded-2xl border border-white/[0.08] bg-white/[0.025] p-5 transition hover:border-white/15 hover:bg-white/[0.04] sm:p-7">
                    <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#B8001F]/10 text-[#FCFAEE]"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2.5a.75.75 0 0 1 .75.75v6.44l4.28 2.47a.75.75 0 1 1-.75 1.3l-4.66-2.7a.75.75 0 0 1-.37-.65V3.25A.75.75 0 0 1 10 2.5Z"/><path fill-rule="evenodd" d="M10 1a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM2.5 10a7.5 7.5 0 1 1 15 0 7.5 7.5 0 0 1-15 0Z" clip-rule="evenodd"/></svg></span>
                    <h2 class="text-lg font-bold">Keep your movie life in order</h2><p class="mt-2 text-sm leading-6 text-white/55">Save favorites, revisit films you love, and make lists for your next movie night.</p>
                </article>
                <article class="rounded-2xl border border-white/[0.08] bg-white/[0.025] p-5 transition hover:border-white/15 hover:bg-white/[0.04] sm:p-7">
                    <span class="mb-6 flex h-11 w-11 items-center justify-center rounded-xl bg-[#B8001F]/10 text-[#FCFAEE]"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a6 6 0 0 0-3.75 10.68c.54.44 1 .97 1.21 1.57h5.08c.21-.6.67-1.13 1.21-1.57A6 6 0 0 0 10 2Z"/><path d="M7.5 16h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1 0-1.5Z"/></svg></span>
                    <h2 class="text-lg font-bold">Share the films that stay with you</h2><p class="mt-2 text-sm leading-6 text-white/55">Write a review, discover what others think, and find your corner of the film community.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="border-t border-white/[0.08]">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-7 text-xs text-white/40 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
            <span>© {{ date('Y') }} Cinevault. Made for film lovers.</span>
            <a href="{{ route('films.index') }}" class="font-semibold text-white/60 transition hover:text-white">Start exploring <span aria-hidden="true">→</span></a>
        </div>
    </footer>
</body>
</html>
