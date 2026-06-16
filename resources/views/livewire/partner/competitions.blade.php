<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gare
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Visualizza le gare disponibili e gestisci le tue iscrizioni come partner.
                </p>
            </div>

            {{-- Statistiche --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gare Iscritte</p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['subscribed'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gare Attive</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="$set('activeTab', 'available')" class="{{ $activeTab === 'available' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Gare Disponibili
                        </button>
                        <button wire:click="$set('activeTab', 'subscribed')" class="{{ $activeTab === 'subscribed' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Le Mie Gare
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Filtri --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="search" value="Cerca" />
                            <x-input wire:model.live.debounce.300ms="search" id="search" class="block mt-1 w-full" placeholder="Nome gara..." />
                        </div>
                        <div>
                            <x-label for="filterStatus" value="Stato" />
                            <select wire:model.live="filterStatus" id="filterStatus" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Tutti</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lista Gare --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($competitions as $competition)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        {{-- Immagine --}}
                        @if($competition->image)
                            <div class="h-40 overflow-hidden">
                                <img src="{{ Storage::url($competition->image) }}" alt="{{ $competition->name }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="h-40 bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                        @endif

                        <div class="p-4">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 line-clamp-2">
                                    {{ $competition->name }}
                                </h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $competition->status->badgeClasses() }}">
                                    {{ $competition->status->label() }}
                                </span>
                            </div>

                            {{-- Ente --}}
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                {{ $competition->ente->name ?? 'N/D' }}
                            </p>

                            {{-- Info --}}
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $competition->start_date->format('d/m/Y') }} - {{ $competition->end_date->format('d/m/Y') }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ $competition->approved_users_count ?? 0 }} partecipanti
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ $competition->approved_partners_count ?? 0 }} partner
                                </div>
                            </div>

                            {{-- Azioni --}}
                            <div class="flex gap-2">
                                <button wire:click="showDetail({{ $competition->id }})" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Dettaglio
                                </button>

                                @if($activeTab === 'available')
                                    <button wire:click="confirmSubscribe({{ $competition->id }})" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Iscriviti
                                    </button>
                                @else
                                    <button wire:click="confirmUnsubscribe({{ $competition->id }})" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-md hover:bg-red-200 dark:bg-red-900 dark:text-red-300 dark:hover:bg-red-800">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                                        </svg>
                                        Disiscriviti
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-8 text-center">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                            @if($activeTab === 'available')
                                <p class="text-gray-500 dark:text-gray-400">Nessuna gara disponibile al momento.</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Le gare disponibili per i partner devono avere modalita "Premi basati su crediti".</p>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">Non sei iscritto a nessuna gara.</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Vai su "Gare Disponibili" per iscriverti come partner.</p>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginazione --}}
            @if($competitions->hasPages())
                <div class="mt-6">
                    {{ $competitions->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Dettaglio Gara --}}
    <x-dialog-modal wire:model.live="showDetailModal" maxWidth="2xl">
        <x-slot name="title">
            {{ $selectedCompetition?->name ?? 'Dettaglio Gara' }}
        </x-slot>

        <x-slot name="content">
            @if($selectedCompetition)
                <div class="space-y-6">
                    {{-- Stato e Ente --}}
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $selectedCompetition->status->badgeClasses() }}">
                            {{ $selectedCompetition->status->label() }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Organizzato da: <strong>{{ $selectedCompetition->ente->name ?? 'N/D' }}</strong>
                        </span>
                    </div>

                    {{-- Date --}}
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Data Inizio</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedCompetition->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Data Fine</p>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedCompetition->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    {{-- Statistiche --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $selectedCompetition->approved_users_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Partecipanti</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $selectedCompetition->approved_partners_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Partner</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($selectedCompetition->total_distance_km ?? 0, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Km Totali</p>
                        </div>
                    </div>

                    {{-- Descrizione --}}
                    @if($selectedCompetition->description)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrizione</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $selectedCompetition->description }}</p>
                        </div>
                    @endif

                    {{-- Info Crediti --}}
                    @if($selectedCompetition->credits_to_euro > 0)
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong>Valore crediti:</strong> {{ number_format($selectedCompetition->credits_to_euro, 2, ',', '.') }} crediti = 1 EUR
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDetailModal', false)">
                Chiudi
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Conferma Iscrizione --}}
    <x-dialog-modal wire:model.live="showSubscribeModal" maxWidth="md">
        <x-slot name="title">
            Conferma Iscrizione
        </x-slot>

        <x-slot name="content">
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                <p class="text-sm text-indigo-700 dark:text-indigo-300">
                    Iscrivendoti come partner a questa gara, sarai visibile agli utenti partecipanti e potrai ricevere richieste di utilizzo crediti.
                </p>
            </div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Confermi di volerti iscrivere come partner a questa gara?
            </p>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showSubscribeModal', false)">
                Annulla
            </x-secondary-button>
            <x-button wire:click="subscribe" class="ml-3 bg-indigo-600 hover:bg-indigo-700">
                Conferma Iscrizione
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Conferma Disiscrizione --}}
    <x-dialog-modal wire:model.live="showUnsubscribeModal" maxWidth="md">
        <x-slot name="title">
            Conferma Disiscrizione
        </x-slot>

        <x-slot name="content">
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-700 dark:text-red-300">
                    <strong>Attenzione:</strong> Disiscrivendoti, non sarai piu visibile come partner per questa gara e non potrai ricevere nuove richieste di utilizzo crediti.
                </p>
            </div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Confermi di volerti disiscrivere da questa gara?
            </p>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showUnsubscribeModal', false)">
                Annulla
            </x-secondary-button>
            <x-danger-button wire:click="unsubscribe" class="ml-3">
                Conferma Disiscrizione
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
