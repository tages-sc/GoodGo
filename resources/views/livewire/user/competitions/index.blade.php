<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-300" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    {{-- Tabs --}}
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button wire:click="switchTab('available')"
                                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'available' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Gare Disponibili
                            </button>
                            <button wire:click="switchTab('mine')"
                                class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm {{ $tab === 'mine' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Le Mie Gare
                            </button>
                        </nav>
                    </div>

                    {{-- Search --}}
                    <div class="mb-6">
                        <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome..." class="w-full max-w-md" />
                    </div>

                    {{-- Competition Cards --}}
                    <div class="space-y-4">
                        @forelse ($competitions as $competition)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($competition->name, 0, 2)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <a href="{{ route('user.competitions.show', $competition) }}" class="text-base font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    {{ $competition->name }}
                                                </a>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $competition->status->badgeClasses() }}">
                                                    {{ $competition->status->label() }}
                                                </span>
                                            </div>
                                            @if($competition->ente)
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $competition->ente->name }}</p>
                                            @endif
                                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span>{{ $competition->start_date->format('d/m/Y') }} - {{ $competition->end_date->format('d/m/Y') }}</span>
                                                @if($competition->participants_count)
                                                    <span>{{ $competition->participants_count }} partecipanti</span>
                                                @endif
                                            </div>
                                            @if($competition->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ Str::limit($competition->description, 120) }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 sm:flex-shrink-0">
                                        <a href="{{ route('user.competitions.show', $competition) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            Dettaglio
                                        </a>
                                        @if($tab === 'available')
                                            @if(!$competition->hasUser($user))
                                                @if($competition->isRegistrationOpen())
                                                    <button wire:click="subscribe({{ $competition->id }})" wire:confirm="Confermi l'iscrizione a '{{ $competition->name }}'?"
                                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                                        Iscriviti
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-md text-xs font-medium text-gray-500 dark:text-gray-400">
                                                        Iscrizioni chiuse
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 bg-green-100 dark:bg-green-900 rounded-md text-xs font-medium text-green-800 dark:text-green-300">
                                                    Iscritto
                                                </span>
                                            @endif
                                        @else
                                            @php
                                                $pivot = $competition->pivot;
                                            @endphp
                                            @if($pivot && $pivot->status === 'pending')
                                                <span class="inline-flex items-center px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900 rounded-md text-xs font-medium text-yellow-800 dark:text-yellow-300">
                                                    In attesa
                                                </span>
                                            @endif
                                            <button wire:click="unsubscribe({{ $competition->id }})" wire:confirm="Confermi la disiscrizione da '{{ $competition->name }}'?"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                                                Disiscriviti
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if($tab === 'mine')
                                        Non sei iscritto a nessuna gara
                                    @else
                                        Nessuna gara disponibile
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    @if($tab === 'mine')
                                        Esplora le gare disponibili e iscriviti per iniziare a competere.
                                    @else
                                        Al momento non ci sono gare pubbliche a cui iscriversi.
                                    @endif
                                </p>
                                @if($tab === 'mine')
                                    <div class="mt-4">
                                        <button wire:click="switchTab('available')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                            Esplora Gare
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-6">
                        {{ $competitions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
