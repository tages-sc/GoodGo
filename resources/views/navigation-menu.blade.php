<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->isSuperAdmin())
                        {{-- Dropdown Gestione --}}
                        <x-nav-dropdown :active="request()->routeIs('admin.enti*') || request()->routeIs('admin.organizers') || request()->routeIs('admin.competitions*') || request()->routeIs('admin.tracks*')">
                            <x-slot name="trigger">Gestione</x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('admin.enti') }}">Enti</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.organizers') }}">Organizzatori</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.competitions') }}">Gare</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.tracks.index') }}">Tracce</x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>

                        {{-- Dropdown Economia --}}
                        <x-nav-dropdown :active="request()->routeIs('admin.movements') || request()->routeIs('admin.spese')">
                            <x-slot name="trigger">Economia</x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('admin.movements') }}">Movimenti</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.spese') }}">Spese</x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>

                        {{-- Dropdown Sistema --}}
                        <x-nav-dropdown :active="request()->routeIs('admin.users*') || request()->routeIs('admin.badges') || request()->routeIs('admin.export') || request()->routeIs('admin.policy-versions') || request()->routeIs('admin.invitation-codes') || request()->routeIs('admin.occupations')">
                            <x-slot name="trigger">Sistema</x-slot>
                            <x-slot name="content">
                                <x-dropdown-link href="{{ route('admin.users.index') }}">Utenti</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.badges') }}">Badge</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.export') }}">Export</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.policy-versions') }}">Policy</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.invitation-codes') }}">Codici Invito</x-dropdown-link>
                                <x-dropdown-link href="{{ route('admin.occupations') }}">Occupazioni</x-dropdown-link>
                            </x-slot>
                        </x-nav-dropdown>
                    @endif

                    @if(Auth::user()->isEnte())
                        <x-nav-link href="{{ route('ente.competitions') }}" :active="request()->routeIs('ente.competitions*')">
                            Gare
                        </x-nav-link>
                        <x-nav-link href="{{ route('ente.organizers') }}" :active="request()->routeIs('ente.organizers')">
                            Organizzatori
                        </x-nav-link>
                        <x-nav-link href="{{ route('ente.members') }}" :active="request()->routeIs('ente.members')">
                            Iscritti
                        </x-nav-link>
                        <x-nav-link href="{{ route('ente.movements') }}" :active="request()->routeIs('ente.movements')">
                            Movimenti
                        </x-nav-link>
                        <x-nav-link href="{{ route('ente.spese') }}" :active="request()->routeIs('ente.spese')">
                            Spese
                        </x-nav-link>
                        <x-nav-link href="{{ route('ente.invitation-codes') }}" :active="request()->routeIs('ente.invitation-codes')">
                            Codici Invito
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->isOrganizer())
                        <x-nav-link href="{{ route('organizer.competitions') }}" :active="request()->routeIs('organizer.competitions*')">
                            Gare
                        </x-nav-link>
                        <x-nav-link href="{{ route('organizer.movements') }}" :active="request()->routeIs('organizer.movements')">
                            Movimenti
                        </x-nav-link>
                        <x-nav-link href="{{ route('organizer.spese') }}" :active="request()->routeIs('organizer.spese')">
                            Spese
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->isUser())
                        <x-nav-link href="{{ route('user.competitions.index') }}" :active="request()->routeIs('user.competitions*')">
                            Gare
                        </x-nav-link>
                        <x-nav-link href="{{ route('user.tracks.index') }}" :active="request()->routeIs('user.tracks*')">
                            Tracce
                        </x-nav-link>
                        <x-nav-link href="{{ route('user.credits') }}" :active="request()->routeIs('user.credits')">
                            Crediti
                        </x-nav-link>
                        <x-nav-link href="{{ route('user.badges') }}" :active="request()->routeIs('user.badges')">
                            Badge
                        </x-nav-link>
                        <x-nav-link href="{{ route('user.movements') }}" :active="request()->routeIs('user.movements')">
                            Movimenti
                        </x-nav-link>
                        <x-nav-link href="{{ route('user.spese') }}" :active="request()->routeIs('user.spese')">
                            Spese
                        </x-nav-link>
                    @endif

                    @if(Auth::user()->isPartner())
                        <x-nav-link href="{{ route('partner.competitions') }}" :active="request()->routeIs('partner.competitions')">
                            Gare
                        </x-nav-link>
                        <x-nav-link href="{{ route('partner.movements') }}" :active="request()->routeIs('partner.movements')">
                            Richieste Spesa
                        </x-nav-link>
                        <x-nav-link href="{{ route('partner.spese') }}" :active="request()->routeIs('partner.spese')">
                            Spese Effettuate
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Settings Dropdown -->
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}

                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-600"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->isSuperAdmin())
                {{-- Gestione --}}
                <div class="pt-2 pb-1 px-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestione</p>
                </div>
                <x-responsive-nav-link href="{{ route('admin.enti') }}" :active="request()->routeIs('admin.enti*')">
                    Enti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.organizers') }}" :active="request()->routeIs('admin.organizers')">
                    Organizzatori
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.competitions') }}" :active="request()->routeIs('admin.competitions*')">
                    Gare
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.tracks.index') }}" :active="request()->routeIs('admin.tracks*')">
                    Tracce
                </x-responsive-nav-link>

                {{-- Economia --}}
                <div class="pt-2 pb-1 px-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Economia</p>
                </div>
                <x-responsive-nav-link href="{{ route('admin.movements') }}" :active="request()->routeIs('admin.movements')">
                    Movimenti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.spese') }}" :active="request()->routeIs('admin.spese')">
                    Spese
                </x-responsive-nav-link>

                {{-- Sistema --}}
                <div class="pt-2 pb-1 px-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sistema</p>
                </div>
                <x-responsive-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users*')">
                    Utenti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.badges') }}" :active="request()->routeIs('admin.badges')">
                    Badge
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.export') }}" :active="request()->routeIs('admin.export')">
                    Export
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.policy-versions') }}" :active="request()->routeIs('admin.policy-versions')">
                    Policy
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.invitation-codes') }}" :active="request()->routeIs('admin.invitation-codes')">
                    Codici Invito
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('admin.occupations') }}" :active="request()->routeIs('admin.occupations')">
                    Occupazioni
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isEnte())
                <x-responsive-nav-link href="{{ route('ente.competitions') }}" :active="request()->routeIs('ente.competitions*')">
                    Gare
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ente.organizers') }}" :active="request()->routeIs('ente.organizers')">
                    Organizzatori
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ente.members') }}" :active="request()->routeIs('ente.members')">
                    Iscritti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ente.movements') }}" :active="request()->routeIs('ente.movements')">
                    Movimenti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ente.spese') }}" :active="request()->routeIs('ente.spese')">
                    Spese
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('ente.invitation-codes') }}" :active="request()->routeIs('ente.invitation-codes')">
                    Codici Invito
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isOrganizer())
                <x-responsive-nav-link href="{{ route('organizer.competitions') }}" :active="request()->routeIs('organizer.competitions*')">
                    Gare
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('organizer.movements') }}" :active="request()->routeIs('organizer.movements')">
                    Movimenti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('organizer.spese') }}" :active="request()->routeIs('organizer.spese')">
                    Spese
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isUser())
                <x-responsive-nav-link href="{{ route('user.competitions.index') }}" :active="request()->routeIs('user.competitions*')">
                    Gare
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('user.tracks.index') }}" :active="request()->routeIs('user.tracks*')">
                    Tracce
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('user.credits') }}" :active="request()->routeIs('user.credits')">
                    Crediti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('user.badges') }}" :active="request()->routeIs('user.badges')">
                    Badge
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('user.movements') }}" :active="request()->routeIs('user.movements')">
                    Movimenti
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('user.spese') }}" :active="request()->routeIs('user.spese')">
                    Spese
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isPartner())
                <x-responsive-nav-link href="{{ route('partner.competitions') }}" :active="request()->routeIs('partner.competitions')">
                    Gare
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('partner.movements') }}" :active="request()->routeIs('partner.movements')">
                    Richieste Spesa
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('partner.spese') }}" :active="request()->routeIs('partner.spese')">
                    Spese Effettuate
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

            </div>
        </div>
    </div>
</nav>
