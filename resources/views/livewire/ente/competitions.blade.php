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
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Le Mie Gare
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuova Gara
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome o descrizione..." class="w-full" />
                        </div>
                        <div>
                            <select wire:model.live="filterStatus" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti gli stati</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tabella --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gara</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organizzatore</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Periodo</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Iscritti</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($competitions as $competition)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                                    {{ strtoupper(substr($competition->name, 0, 2)) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $competition->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($competition->description, 50) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($competition->organizer)
                                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $competition->organizer->name }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400 italic">
                                                    Gestita direttamente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $competition->start_date->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $competition->end_date->format('d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->approved_users_count }}</span>
                                            @if($competition->pending_users_count > 0)
                                                <span class="ml-1 px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 rounded-full">+{{ $competition->pending_users_count }}</span>
                                            @endif
                                            @if($competition->max_participants)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">/{{ $competition->max_participants }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $competition->status->badgeClasses() }}">
                                                {{ $competition->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                {{-- Status dropdown --}}
                                                <select wire:change="updateStatus({{ $competition->id }}, $event.target.value)" class="text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                    @foreach($statuses as $value => $label)
                                                        <option value="{{ $value }}" {{ $competition->status->value === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <a href="{{ route('ente.competitions.show', $competition) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Dettaglio">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('ente.competitions.participants', $competition) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Partecipanti">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('ente.competitions.tracks', $competition) }}" class="text-emerald-600 hover:text-emerald-900 dark:text-emerald-400 dark:hover:text-emerald-300" title="Tracce">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                    </svg>
                                                </a>
                                                <button wire:click="openCloneModal({{ $competition->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Clona">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                                @if(!in_array($competition->status->value, ['active', 'ended', 'cancelled']))
                                                <button wire:click="edit({{ $competition->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifica">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                @endif
                                                <button wire:click="confirmDelete({{ $competition->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Elimina">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessuna gara trovata.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $competitions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="4xl">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Gara' : 'Nuova Gara' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                {{-- Info Base --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-label for="name" value="Nome Gara *" />
                        <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="organizer_id" value="Organizzatore (opzionale)" />
                        <select wire:model="organizer_id" id="organizer_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Gestita direttamente dall'ente</option>
                            @foreach($organizers as $organizer)
                                @php
                                    $hasActive = $organizer->hasActiveCompetition();
                                    $isCurrentEdit = $editingId && $organizer->assignedCompetitions()->where('id', $editingId)->exists();
                                @endphp
                                <option value="{{ $organizer->id }}" {{ $hasActive && !$isCurrentEdit ? 'disabled' : '' }}>
                                    {{ $organizer->name }}{{ $hasActive ? ' (ha gia una gara in corso)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error for="organizer_id" class="mt-2" />
                        @if($organizers->isEmpty())
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Non hai ancora creato organizzatori</p>
                        @endif
                    </div>
                    <div>
                        <x-label for="status" value="Stato *" />
                        <select wire:model="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="status" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-label for="description" value="Descrizione" />
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                {{-- Tipo Gara e Configurazione --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tipo Gara e Configurazione</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <x-label for="reward_mode" value="Modalita Premi *" />
                            <select wire:model="reward_mode" id="reward_mode" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($rewardModes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="reward_mode" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="extension_type" value="Estensione Territoriale" />
                            <select wire:model.live="extension_type" id="extension_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Seleziona...</option>
                                @foreach($extensionTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="extension_type" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="competition_type" value="Tipo Gara" />
                            <select wire:model="competition_type" id="competition_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Seleziona...</option>
                                @foreach($competitionTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="competition_type" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="scoring_type" value="Tipo Punteggio *" />
                            <select wire:model="scoring_type" id="scoring_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($scoringTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="scoring_type" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="leaderboard_type" value="Tipo Classifica" />
                            <select wire:model="leaderboard_type" id="leaderboard_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="km">Chilometri (km)</option>
                                <option value="co2">CO2 risparmiata</option>
                                <option value="gekoin">GeKoin (crediti)</option>
                            </select>
                            <x-input-error for="leaderboard_type" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <x-checkbox wire:model="request_more_data" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Richiedi dati aggiuntivi all'iscrizione</span>
                        </label>
                    </div>
                    <div class="mt-4">
                        <x-label value="Range di Eta" />
                        <div class="flex flex-wrap gap-4 mt-2">
                            @foreach(['<19' => 'Meno di 19', '19-30' => '19-30', '30-65' => '30-65', '65+' => '65+'] as $value => $label)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model.live="age_ranges" value="{{ $value }}" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error for="age_ranges" class="mt-2" />
                    </div>

                    {{-- Selettore Territori (visibile in base a extension_type) --}}
                    @if($extension_type && $extension_type !== 'national')
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Territori Ammessi</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if(isset($territories['regions']))
                            <div>
                                <x-label value="Regioni" />
                                <select wire:model.live="allowed_region_ids" multiple class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" size="6">
                                    @foreach($territories['regions'] as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tieni premuto Ctrl/Cmd per selezione multipla</p>
                            </div>
                            @endif

                            @if(isset($territories['provinces']))
                            <div>
                                <x-label value="Province" />
                                <select wire:model.live="allowed_province_ids" multiple class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" size="6">
                                    @foreach($territories['provinces'] as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }} ({{ $province->code }})</option>
                                    @endforeach
                                </select>
                                @if($extension_type === 'municipal' && empty($allowed_province_ids))
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Seleziona una provincia per vedere i comuni</p>
                                @endif
                            </div>
                            @endif

                            @if(isset($territories['municipalities']) && $territories['municipalities']->isNotEmpty())
                            <div>
                                <x-label value="Comuni" />
                                <select wire:model="allowed_municipality_ids" multiple class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm" size="6">
                                    @foreach($territories['municipalities'] as $municipality)
                                        <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        @if($extension_type === 'regional')
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Seleziona le regioni in cui la gara e valida. Lascia vuoto per tutte.</p>
                        @elseif($extension_type === 'provincial')
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Seleziona le province in cui la gara e valida. Puoi filtrare per regione.</p>
                        @elseif($extension_type === 'municipal')
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Seleziona i comuni in cui la gara e valida. Filtra per regione e provincia.</p>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Date --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Date Gara</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="start_date" value="Data Inizio *" />
                            <x-input wire:model="start_date" id="start_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="start_date" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="end_date" value="Data Fine *" />
                            <x-input wire:model="end_date" id="end_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="end_date" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="registration_start" value="Apertura Iscrizioni" />
                            <x-input wire:model="registration_start" id="registration_start" type="datetime-local" class="mt-1 block w-full" />
                            <x-input-error for="registration_start" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="registration_end" value="Chiusura Iscrizioni" />
                            <x-input wire:model="registration_end" id="registration_end" type="datetime-local" class="mt-1 block w-full" />
                            <x-input-error for="registration_end" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Impostazioni --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Impostazioni</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="max_participants" value="Max Partecipanti" />
                            <x-input wire:model="max_participants" id="max_participants" type="number" class="mt-1 block w-full" placeholder="Illimitato" />
                            <x-input-error for="max_participants" class="mt-2" />
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center">
                                <x-checkbox wire:model="is_public" />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Gara pubblica</span>
                            </label>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center">
                                <x-checkbox wire:model="moderated_subscription" />
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Iscrizioni moderate</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Impostazioni Tracce --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Impostazioni Tracce</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="min_track_distance" value="Distanza Minima (m)" />
                            <x-input wire:model="min_track_distance" id="min_track_distance" type="number" class="mt-1 block w-full" placeholder="Nessun limite" />
                            <x-input-error for="min_track_distance" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="max_track_distance" value="Distanza Massima (m)" />
                            <x-input wire:model="max_track_distance" id="max_track_distance" type="number" class="mt-1 block w-full" placeholder="Nessun limite" />
                            <x-input-error for="max_track_distance" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="max_daily_tracks" value="Max Tracce/Giorno *" />
                            <x-input wire:model="max_daily_tracks" id="max_daily_tracks" type="number" min="1" max="10" class="mt-1 block w-full" />
                            <x-input-error for="max_daily_tracks" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-label value="Mezzi di Trasporto Ammessi" />
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach($transportModes as $value => $label)
                                <label class="flex items-center">
                                    <x-checkbox wire:model="allowed_transport_modes" value="{{ $value }}" />
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lascia vuoto per permettere tutti i mezzi</p>
                    </div>

                    {{-- Limiti Distanza per Modalita --}}
                    <div class="mt-4">
                        <x-label value="Distanza Max per Traccia per Modalita (m) - opzionale" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Sovrascrive il limite generale per modalita specifiche</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                            @foreach($transportModes as $value => $label)
                                <div>
                                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</label>
                                    <x-input wire:model="max_distance_per_mode.{{ $value }}" type="number" class="mt-1 block w-full text-sm" placeholder="-" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Limiti Distanza Giornaliera per Modalita --}}
                    <div class="mt-4">
                        <x-label value="Distanza Max Giornaliera per Modalita (m) - opzionale" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Limite giornaliero di distanza accreditabile per modalita</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                            @foreach($transportModes as $value => $label)
                                <div>
                                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</label>
                                    <x-input wire:model="max_daily_distance_per_mode.{{ $value }}" type="number" class="mt-1 block w-full text-sm" placeholder="-" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Crediti --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Crediti e Conversione</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <x-label for="credits_per_km" value="Crediti per Km (default)" />
                            <x-input wire:model="credits_per_km" id="credits_per_km" type="number" step="0.0001" class="mt-1 block w-full" placeholder="Default sistema" />
                            <x-input-error for="credits_per_km" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="credits_multiplier" value="Moltiplicatore Crediti *" />
                            <x-input wire:model="credits_multiplier" id="credits_multiplier" type="number" step="0.01" min="0.01" max="10" class="mt-1 block w-full" />
                            <x-input-error for="credits_multiplier" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="credits_to_euro" value="Tasso Crediti -> Euro" />
                            <x-input wire:model="credits_to_euro" id="credits_to_euro" type="number" step="0.01" class="mt-1 block w-full" placeholder="es. 0.01" />
                            <x-input-error for="credits_to_euro" class="mt-2" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">1 credito = X euro</p>
                        </div>
                        <div>
                            <x-label for="max_earning_per_person" value="Max Guadagno/Persona (EUR)" />
                            <x-input wire:model="max_earning_per_person" id="max_earning_per_person" type="number" step="0.01" class="mt-1 block w-full" placeholder="Nessun limite" />
                            <x-input-error for="max_earning_per_person" class="mt-2" />
                        </div>
                    </div>

                    {{-- Crediti per Modalita --}}
                    <div class="mt-4">
                        <x-label value="Crediti per Km per Modalita (opzionale)" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Sovrascrive il valore default per modalita specifiche</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                            @foreach($transportModes as $value => $label)
                                <div>
                                    <label class="text-xs text-gray-600 dark:text-gray-400">{{ $label }}</label>
                                    <x-input wire:model="credits_per_mode.{{ $value }}" type="number" step="0.0001" class="mt-1 block w-full text-sm" placeholder="-" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Regolamento e Premi --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Regolamento e Premi</h4>
                    <div class="space-y-4">
                        <div>
                            <x-label for="rules_document" value="Regolamento (PDF)" />
                            <input wire:model="rules_document" id="rules_document" type="file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="rules_document" class="mt-2" />
                            @if($editingId)
                                @php $comp = \App\Models\Competition::find($editingId); @endphp
                                @if($comp?->rules_document)
                                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                        PDF attuale: <a href="{{ Storage::url($comp->rules_document) }}" target="_blank" class="underline">Visualizza</a>
                                    </p>
                                @endif
                            @endif
                        </div>
                        <div>
                            <x-label for="rules" value="Note Regolamento (testo opzionale)" />
                            <textarea wire:model="rules" id="rules" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Note aggiuntive al regolamento..."></textarea>
                            <x-input-error for="rules" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="prizes" value="Premi" />
                            <textarea wire:model="prizes" id="prizes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                            <x-input-error for="prizes" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Documenti e Media --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Documenti e Media</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="image" value="Immagine Principale" />
                            <input wire:model="image" id="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="image" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="banner" value="Banner" />
                            <input wire:model="banner" id="banner" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="banner" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="extra_document" value="Documento Aggiuntivo (PDF, DOC)" />
                            <input wire:model="extra_document" id="extra_document" type="file" accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="extra_document" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="questionnaire_url" value="URL Questionario (opzionale)" />
                            <x-input wire:model="questionnaire_url" id="questionnaire_url" type="url" class="mt-1 block w-full" placeholder="https://..." />
                            <x-input-error for="questionnaire_url" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeModal">
                Annulla
            </x-secondary-button>

            <x-button class="ms-3" wire:click="save">
                {{ $editingId ? 'Aggiorna' : 'Crea' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Conferma Eliminazione --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            Elimina Gara
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questa gara? Tutti i dati associati (iscritti, tracce, statistiche) verranno rimossi. Questa azione non puo essere annullata.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)">
                Annulla
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="delete">
                Elimina
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

    {{-- Modal Clona Gara --}}
    <x-dialog-modal wire:model.live="showCloneModal">
        <x-slot name="title">
            Clona Gara
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Stai per creare una copia di questa gara. Inserisci il nuovo nome e le nuove date.
                </p>
                <div>
                    <x-label for="clone_name" value="Nome Nuova Gara *" />
                    <x-input wire:model="clone_name" id="clone_name" type="text" class="mt-1 block w-full" />
                    <x-input-error for="clone_name" class="mt-2" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="clone_start_date" value="Data Inizio *" />
                        <x-input wire:model="clone_start_date" id="clone_start_date" type="date" class="mt-1 block w-full" />
                        <x-input-error for="clone_start_date" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="clone_end_date" value="Data Fine *" />
                        <x-input wire:model="clone_end_date" id="clone_end_date" type="date" class="mt-1 block w-full" />
                        <x-input-error for="clone_end_date" class="mt-2" />
                    </div>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showCloneModal', false)">
                Annulla
            </x-secondary-button>

            <x-button class="ms-3" wire:click="cloneCompetition">
                Clona Gara
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
