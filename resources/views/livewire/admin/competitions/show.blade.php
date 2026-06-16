<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.competitions') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $competition->name }}
                    </h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $competition->status->badgeClasses() }}">
                        {{ $competition->status->label() }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.tracks.index', ['competition_id' => $competition->id]) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Tracce
                    </a>
                </div>
            </div>

            {{-- Main Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Info Generali --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informazioni Generali</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stato</p>
                                    <p class="mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $competition->status->badgeClasses() }}">
                                            {{ $competition->status->label() }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ente</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->ente->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Organizzatore</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->organizer->name ?? 'Non assegnato' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Inizio</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->start_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Fine</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->end_date->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Apertura Iscrizioni</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->registration_start ? $competition->registration_start->format('d/m/Y H:i') : 'Non specificata' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Chiusura Iscrizioni</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->registration_end ? $competition->registration_end->format('d/m/Y H:i') : 'Non specificata' }}</p>
                                </div>
                            </div>

                            @if($competition->description)
                                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Descrizione</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $competition->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Regolamento --}}
                    @if($competition->rules)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Regolamento</h3>
                                <div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $competition->rules }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Premi --}}
                    @if($competition->prizes)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Premi</h3>
                                <div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $competition->prizes }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Configurazione --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Configurazione</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Modalita Premi</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->reward_mode->label() }}</p>
                                </div>
                                @if($competition->extension_type)
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estensione Territoriale</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->extension_type->label() }}</p>
                                    </div>
                                @endif
                                @if($competition->competition_type)
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Gara</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->competition_type->label() }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Punteggio</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->scoring_type->label() }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo Classifica</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ match($competition->leaderboard_type) { 'km' => 'Chilometri (km)', 'co2' => 'CO2 risparmiata', 'gekoin' => 'GeKoin (crediti)', default => $competition->leaderboard_type } }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Richiedi Dati Aggiuntivi</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->request_more_data ? 'Si' : 'No' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gara Pubblica</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->is_public ? 'Si' : 'No' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Iscrizioni Moderate</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->moderated_subscription ? 'Si' : 'No' }}</p>
                                </div>
                                @if($competition->max_participants)
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Max Partecipanti</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $competition->max_participants }}</p>
                                    </div>
                                @endif
                                @if($competition->age_range)
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Fascia Eta</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ is_array($competition->age_range) ? implode(', ', $competition->age_range) : '-' }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Mezzi di Trasporto --}}
                            @if($competition->allowed_transport_modes && count($competition->allowed_transport_modes) > 0)
                                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Mezzi di Trasporto Ammessi</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($competition->allowed_transport_modes as $mode)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                                {{ $this->getTransportModeLabel($mode) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Crediti --}}
                            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Crediti e Conversione</p>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Crediti/Km</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->credits_per_km ?? 'Default' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Moltiplicatore</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->credits_multiplier ?? '-' }}x</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Crediti -> Euro</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->credits_to_euro ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Max Guadagno/Persona</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            @if($competition->max_earning_per_person)
                                                {{ number_format($competition->max_earning_per_person, 2) }} EUR
                                            @else
                                                Illimitato
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Crediti per Modalita --}}
                            @if($competition->credits_per_mode && count($competition->credits_per_mode) > 0)
                                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Crediti per Km per Modalita</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                                        @foreach($competition->credits_per_mode as $mode => $credits)
                                            @if($credits)
                                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getTransportModeLabel($mode) }}</p>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $credits }}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Documenti e Media --}}
                    @if($competition->image || $competition->banner || $competition->extra_document || $competition->questionnaire_url)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Documenti e Media</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($competition->image)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Immagine Principale</p>
                                            <img src="{{ asset('storage/' . $competition->image) }}" alt="Immagine gara" class="rounded-lg max-h-48 object-cover">
                                        </div>
                                    @endif
                                    @if($competition->banner)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Banner</p>
                                            <img src="{{ asset('storage/' . $competition->banner) }}" alt="Banner gara" class="rounded-lg max-h-48 object-cover">
                                        </div>
                                    @endif
                                    @if($competition->extra_document)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Documento Aggiuntivo</p>
                                            <a href="{{ asset('storage/' . $competition->extra_document) }}" target="_blank" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Scarica documento
                                            </a>
                                        </div>
                                    @endif
                                    @if($competition->questionnaire_url)
                                        <div>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Questionario</p>
                                            <a href="{{ $competition->questionnaire_url }}" target="_blank" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Apri questionario
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column (1/3) --}}
                <div class="space-y-6">

                    {{-- Statistiche --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Statistiche</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Partecipanti</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $competition->participants_count ?? 0 }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Tracce Totali</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $tracksCount }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-emerald-100 dark:bg-emerald-900 rounded-lg">
                                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Tracce Valide</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $validTracksCount }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Distanza Totale</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($competition->total_distance_km ?? 0, 1) }} km</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-2 bg-teal-100 dark:bg-teal-900 rounded-lg">
                                            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">CO2 Risparmiata</span>
                                    </div>
                                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($competition->total_co2_saved_kg ?? 0, 1) }} kg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Azioni Rapide --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Azioni Rapide</h3>
                            <div class="space-y-3">
                                <a href="{{ route('admin.tracks.index', ['competition_id' => $competition->id]) }}" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    Gestione Tracce
                                </a>
                                <a href="{{ route('admin.competitions') }}" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    Torna alla Lista Gare
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Impostazioni Tracce --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Impostazioni Tracce</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Max Tracce/Giorno</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->max_daily_tracks ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Distanza Min (m)</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->min_track_distance ?? 'Nessun limite' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Distanza Max (m)</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->max_track_distance ?? 'Nessun limite' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Classifica (full width) --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Classifica
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Top 50 - ordinata per {{ match($competition->leaderboard_type) { 'co2' => 'CO2 risparmiata', 'gekoin' => 'GeKoin', default => 'km' } }})</span>
                    </h3>

                    @if($leaderboard->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                            Nessun partecipante in classifica.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pos.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distanza (km)</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">CO2 Risparmiata (kg)</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tracce</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($leaderboard as $index => $user)
                                        <tr class="{{ $index < 3 ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($index === 0)
                                                        <span class="text-yellow-500 font-bold text-lg">1</span>
                                                    @elseif($index === 1)
                                                        <span class="text-gray-400 font-bold text-lg">2</span>
                                                    @elseif($index === 2)
                                                        <span class="text-amber-700 font-bold text-lg">3</span>
                                                    @else
                                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $this->obfuscateName($user->name) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $this->obfuscateEmail($user->email) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_credits ?? 0, 1) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_distance_km ?? 0, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($user->pivot->total_co2_saved_kg ?? 0, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $user->pivot->tracks_count ?? 0 }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
