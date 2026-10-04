<nav aria-label="Main navigation" class="dark bg-neutral-primary fixed w-full z-50 top-0 start-0 border-b border-default text-heading">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-3">
        <a href="{{ route('films.index') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-white">
                <x-application-logo class="h-5 w-5" aria-hidden="true" />
            </span>
            <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">CINEVAULT</span>
        </a>

        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button" class="flex text-sm bg-neutral-primary rounded-full md:me-0 focus:ring-4 focus:ring-neutral-tertiary" id="client-user-menu-button" aria-expanded="false" data-dropdown-toggle="client-user-dropdown" data-dropdown-placement="bottom-end">
                <span class="sr-only">Open user menu</span>
                <x-user-avatar :user="auth()->user()" />
            </button>
            <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-52" id="client-user-dropdown">
                @auth
                    <div class="px-4 py-3 text-sm border-b border-default">
                        <span class="block text-heading font-medium">{{ auth()->user()->name }}</span>
                        <span class="block text-body truncate">{{ auth()->user()->email }}</span>
                    </div>
                    <ul class="p-2 text-sm text-body font-medium" aria-labelledby="client-user-menu-button">
                        <li><a href="{{ route('profile.edit') }}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Profile</a></li>
                        <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex items-center w-full p-2 text-start hover:bg-neutral-tertiary-medium hover:text-heading rounded">Log out</button></form></li>
                    </ul>
                @else
                    <ul class="p-2 text-sm text-body font-medium" aria-labelledby="client-user-menu-button">
                        <li><a href="{{ route('login') }}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Sign up</a></li>
                    </ul>
                @endauth
            </div>
            <button data-collapse-toggle="client-navbar" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-body rounded-base md:hidden hover:bg-neutral-secondary-soft hover:text-heading focus:outline-none focus:ring-2 focus:ring-neutral-tertiary" aria-controls="client-navbar" aria-expanded="false">
                <span class="sr-only">Open main menu</span>
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/></svg>
            </button>
        </div>

        <div class="items-center justify-between hidden w-full bg-neutral-primary md:flex md:w-auto md:order-1" id="client-navbar">
            <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-default rounded-base bg-neutral-secondary-soft md:flex-row md:space-x-4 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-neutral-primary">
                @foreach ([
                    ['films.index', 'films.*', 'Films'],
                    ['lists.index', 'lists.*', 'Lists'],
                    ['members.index', 'members.*', 'Members'],
                    ['teasers.index', 'teasers.*', 'Teasers'],
                ] as [$route, $pattern, $label])
                    @php($active = request()->routeIs($pattern))
                    <li>
                        <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif @class([
                            'block py-2 px-3 rounded md:p-2 transition',
                            'text-white bg-brand md:text-fg-brand md:bg-neutral-tertiary-soft' => $active,
                            'text-heading hover:bg-neutral-tertiary md:hover:bg-neutral-tertiary-soft md:hover:text-fg-brand' => ! $active,
                        ])>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
