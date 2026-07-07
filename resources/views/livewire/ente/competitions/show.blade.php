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

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route($backRoute) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Torna alle Gare
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route($participantsRoute, $competition) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Partecipanti
                    </a>
                    <a href="{{ route($tracksRoute, $competition) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Tracce
                    </a>
                </div>
            </div>

            {{-- Title + Status --}}
            <div class="mb-6 flex items-center gap-4">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $competition->name }}
                </h3>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $competition->status->badgeClasses() }}">
                    {{ $competition->status->label() }}
                </span>
            </div>

            {{-- Grid: Info + Stats --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Left Column: Info --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informazioni Generali --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informazioni Generali</h4>

                            @if($competition->banner)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($competition->banner) }}" alt="Banner" class="w-full h-48 object-cover rounded-lg" />
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ente</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->ente?->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Organizzatore</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->organizer?->name ?? 'Gestita direttamente' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Inizio</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->start_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Fine</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->end_date->format('d/m/Y') }}</p>
                                </div>
                                @if($competition->registration_start)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Apertura Iscrizioni</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ local_dt($competition->registration_start, 'd/m/Y H:i') }}</p>
                                    </div>
                                @endif
                                @if($competition->registration_end)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Chiusura Iscrizioni</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ local_dt($competition->registration_end, 'd/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($competition->description)
                                <div class="mt-4">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Descrizione</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 whitespace-pre-line">{{ $competition->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Configurazione --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Configurazione</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @if($competition->reward_mode)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Modalita Premi</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->reward_mode->label() }}</p>
                                    </div>
                                @endif
                                @if($competition->extension_type)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estensione Territoriale</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->extension_type->label() }}</p>
                                    </div>
                                @endif
                                @if($competition->competition_type)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Gara</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->competition_type->label() }}</p>
                                    </div>
                                @endif
                                @if($competition->scoring_type)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Punteggio</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->scoring_type->label() }}</p>
                                    </div>
                                @endif
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Classifica</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ match($competition->leaderboard_type) { 'km' => 'Chilometri (km)', 'co2' => 'CO2 risparmiata', 'gekoin' => 'GeKoin (crediti)', default => $competition->leaderboard_type } }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Richiedi Dati Aggiuntivi</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->request_more_data ? 'Si' : 'No' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Visibilita</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->is_public ? 'Pubblica' : 'Privata' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Iscrizioni Moderate</span>
                                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->moderated_subscription ? 'Si' : 'No' }}</p>
                                </div>
                                @if($competition->max_participants)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Max Partecipanti</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $competition->max_participants }}</p>
                                    </div>
                                @endif
                                @if($competition->age_range)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fascia di Eta</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ is_array($competition->age_range) ? implode(', ', $competition->age_range) : '-' }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Mezzi di trasporto ammessi --}}
                            @if($competition->allowed_transport_modes && count($competition->allowed_transport_modes) > 0)
                                <div class="mt-4">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Mezzi di Trasporto Ammessi</span>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($competition->allowed_transport_modes as $mode)
                                            <span class="px-2 py-1 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 rounded-full">
                                                {{ \App\Enums\TransportMode::from($mode)->label() }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Crediti --}}
                            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block mb-2">Configurazione Crediti</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @if($competition->credits_per_km)
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Crediti/Km</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->credits_per_km }}</p>
                                        </div>
                                    @endif
                                    @if($competition->credits_multiplier)
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Moltiplicatore</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">x{{ $competition->credits_multiplier }}</p>
                                        </div>
                                    @endif
                                    @if($competition->credits_to_euro)
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Crediti -> Euro</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->credits_to_euro }} EUR</p>
                                        </div>
                                    @endif
                                    @if($competition->max_earning_per_person)
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Max Guadagno/Persona</span>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->max_earning_per_person }} EUR</p>
                                        </div>
                                    @endif
                                </div>

                                @if($competition->credits_per_mode && count(array_filter($competition->credits_per_mode)) > 0)
                                    <div class="mt-3">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Crediti per Km per Modalita</span>
                                        <div class="flex flex-wrap gap-3 mt-1">
                                            @foreach($competition->credits_per_mode as $mode => $credits)
                                                @if($credits)
                                                    <span class="text-xs text-gray-700 dark:text-gray-300">
                                                        {{ \App\Enums\TransportMode::from($mode)->label() }}: <strong>{{ $credits }}</strong>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Regolamento e Premi --}}
                    @if($competition->rules || $competition->rules_document || $competition->prizes)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Regolamento e Premi</h4>

                                @if($competition->rules)
                                    <div class="mb-4">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Regolamento</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 whitespace-pre-line">{{ $competition->rules }}</p>
                                    </div>
                                @endif

                                @if($competition->rules_document)
                                    <div class="mb-4">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Regolamento (PDF)</span>
                                        <div class="flex items-center gap-3 mt-1">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <a href="{{ Storage::url($competition->rules_document) }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Scarica il regolamento
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                @if($competition->prizes)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Premi</span>
                                        <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 whitespace-pre-line">{{ $competition->prizes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Documenti --}}
                    @if($competition->image || $competition->extra_document || $competition->questionnaire_url)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Documenti e Media</h4>
                                <div class="space-y-3">
                                    @if($competition->image)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <a href="{{ Storage::url($competition->image) }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Immagine Principale
                                            </a>
                                        </div>
                                    @endif
                                    @if($competition->extra_document)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <a href="{{ Storage::url($competition->extra_document) }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Documento Aggiuntivo
                                            </a>
                                        </div>
                                    @endif
                                    @if($competition->questionnaire_url)
                                        <div class="flex items-center gap-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                            <a href="{{ $competition->questionnaire_url }}" target="_blank" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Questionario
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Statistics --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Statistiche</h4>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Partecipanti</span>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $competition->participants_count ?? 0 }}</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-emerald-100 dark:bg-emerald-800 rounded-lg">
                                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Tracce</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $validTracksCount }} valide</span>
                                        </div>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $tracksCount }}</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Distanza Totale</span>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($competition->total_distance_km ?? 0, 1) }} km</span>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">CO2 Risparmiata</span>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($competition->total_co2_saved_kg ?? 0, 1) }} kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Immagine --}}
                    @if($competition->image)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Immagine</h4>
                                <img src="{{ Storage::url($competition->image) }}" alt="{{ $competition->name }}" class="w-full rounded-lg" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Classifica --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Classifica
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Top 50 - ordinata per {{ match($competition->leaderboard_type) { 'co2' => 'CO2 risparmiata', 'gekoin' => 'GeKoin', default => 'km' } }})</span>
                    </h4>

                    @if($leaderboard->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pos.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utente</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distanza</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">CO2 Risp.</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tracce</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($leaderboard as $index => $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($index < 3)
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : ($index === 1 ? 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300') }} font-bold text-sm">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-gray-500 dark:text-gray-400 w-8 text-center">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $this->obfuscateName($user->name) }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $this->obfuscateEmail($user->email) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_credits ?? 0, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_distance_km ?? 0, 2) }} km</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_co2_saved_kg ?? 0, 2) }} kg</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $user->pivot->tracks_count ?? 0 }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                            Nessun partecipante in classifica.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
