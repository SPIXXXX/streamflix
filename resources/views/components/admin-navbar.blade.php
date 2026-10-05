<nav aria-label="Admin navigation" class="dark bg-neutral-primary fixed w-full z-50 top-0 start-0 border-b border-default text-heading">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-3">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-white">
                <x-application-logo class="h-5 w-5" aria-hidden="true" />
            </span>
            <span class="self-center text-xl text-heading font-semibold whitespace-nowrap">CINEVAULT</span>
        </a>

        <div class="flex items-center md:order-2 space-x-3 md:space-x-0 rtl:space-x-reverse">
            <button type="button" class="flex text-sm bg-neutral-primary rounded-full md:me-0 focus:ring-4 focus:ring-neutral-tertiary" id="admin-user-menu-button" aria-expanded="false" data-dropdown-toggle="admin-user-dropdown" data-dropdown-placement="bottom-end">
                <span class="sr-only">Open admin user menu</span>
                <span class="relative inline-flex">
                        <x-user-avatar :user="auth()->user()" :online="auth()->user()->is_online" />
                    <span role="img" aria-label="Online" title="Online" class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-sf-bg bg-emerald-400"></span>
                </span>
            </button>
            <div class="z-50 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-52" id="admin-user-dropdown">
                <div class="px-4 py-3 text-sm border-b border-default">
                    <span class="block text-heading font-medium">{{ auth()->user()?->name ?? 'Admin' }}</span>
                    <span class="block text-body truncate">{{ auth()->user()?->email }}</span>
                </div>
                <ul class="p-2 text-sm text-body font-medium" aria-labelledby="admin-user-menu-button">
                    <li><a href="{{ route('profile.edit') }}" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Profile</a></li>
                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex items-center w-full p-2 text-start hover:bg-neutral-tertiary-medium hover:text-heading rounded">Log out</button></form></li>
                </ul>
            </div>
            <button data-collapse-toggle="admin-navbar" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-body rounded-base md:hidden hover:bg-neutral-secondary-soft hover:text-heading focus:outline-none focus:ring-2 focus:ring-neutral-tertiary" aria-controls="admin-navbar" aria-expanded="false">
                <span class="sr-only">Open admin menu</span>
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/></svg>
            </button>
        </div>

        <div class="items-center justify-between hidden w-full bg-neutral-primary md:flex md:w-auto md:order-1" id="admin-navbar">
            <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-default rounded-base bg-neutral-secondary-soft md:flex-row md:space-x-2 lg:space-x-4 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-neutral-primary">
                @foreach ([
                    ['admin.dashboard', 'Dashboard'],
                    ['admin.films.index', 'Films'],
                    ['admin.lists.index', 'Lists'],
                    ['admin.accounts.index', 'Accounts'],
                    ['admin.teasers.index', 'Teasers'],
                ] as [$route, $label])
                    @php
                        $active = request()->routeIs($route)
                            || ($route === 'admin.films.index' && request()->routeIs('admin.films.edit'))
                            || ($route === 'admin.films.create' && request()->routeIs('admin.films.create'))
                            || ($route === 'admin.lists.index' && request()->routeIs('admin.lists.*'))
                            || ($route === 'admin.accounts.index' && request()->routeIs('admin.accounts.show'))
                            || ($route === 'admin.teasers.index' && request()->routeIs('admin.teasers.edit'));
                    @endphp
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
