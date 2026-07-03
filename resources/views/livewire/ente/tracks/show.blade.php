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
            <div class="mb-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route($backRouteName, $competition) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Traccia #{{ $track->id }}
                    </h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $this->getStatusBadgeClass() }}">
                        {{ $track->status->label() }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    {{-- Pulsante Test Validazione --}}
                    <x-secondary-button wire:click="openTestModal" class="border-purple-500 text-purple-600 hover:bg-purple-50 dark:border-purple-400 dark:text-purple-400 dark:hover:bg-purple-900/20">
                        Test Validazione
                    </x-secondary-button>

                    {{-- Pulsante Valida/Modifica Stato --}}
                    <x-button wire:click="openValidateModal" class="{{ $track->canBeValidated() ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }}">
                        {{ $track->canBeValidated() ? 'Valida/Invalida' : 'Modifica Stato' }}
                    </x-button>
                </div>
            </div>

            {{-- Contenuto principale --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Colonna Mappa (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Mappa Percorso</h3>
                            <div id="track-map" style="height: 450px; border-radius: 0.5rem;" class="bg-gray-100 dark:bg-gray-700"></div>

                            {{-- Legenda --}}
                            <div class="mt-3 flex flex-wrap gap-3 text-xs">
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1"></span> Piedi</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span> Bici</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-amber-500 mr-1"></span> Treno</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-violet-500 mr-1"></span> Bus</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span> Auto</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-orange-500 mr-1"></span> Moto</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Colonna Info (1/3) --}}
                <div class="space-y-6">
                    {{-- Info Traccia --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Dettagli</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Utente</dt>
                                <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ $track->user->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->user->email }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Gara</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->competition?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Inizio</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ local_dt($track->started_at, 'd/m/Y H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Fine</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ local_dt($track->ended_at, 'd/m/Y H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Durata</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->duration_formatted }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Distanza totale</dt>
                                <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($track->total_distance_km, 2) }} km</dd>
                            </div>
                            @if($track->valid_distance_meters > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Distanza valida</dt>
                                    <dd class="text-green-600 dark:text-green-400 font-medium">{{ number_format($track->valid_distance_km, 2) }} km</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Multimodale</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->is_multimodal ? 'Si' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Crediti & Emissioni --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Crediti & Emissioni</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Crediti guadagnati</dt>
                                <dd class="text-green-600 dark:text-green-400 font-bold">{{ number_format($track->credits_earned ?? 0, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">CO2 risparmiata</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ number_format(($track->co2_saved_grams ?? 0) / 1000, 3) }} kg</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Calorie bruciate</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ number_format($track->calories_burned ?? 0, 0) }} kcal</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Validazione --}}
                    @if($track->validated_at || $track->rejection_reason)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Validazione</h3>
                            <dl class="space-y-2 text-sm">
                                @if($track->validated_at)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Validata il</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ local_dt($track->validated_at, 'd/m/Y H:i') }}</dd>
                                    </div>
                                @endif
                                @if($track->validator)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Validata da</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $track->validator->name }}</dd>
                                    </div>
                                @endif
                                @if($track->rejection_reason)
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400 mb-1">Motivazione</dt>
                                        <dd class="text-red-600 dark:text-red-400 text-xs bg-red-50 dark:bg-red-900/20 rounded p-2">{{ $track->rejection_reason }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabella Segmenti --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Segmenti ({{ $track->segments->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Modalita</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Distanza</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Durata</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vel. Media</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vel. Max</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($track->segments as $segment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $segment->sequence }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                @switch($segment->transport_mode->value)
                                                    @case('walk') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                    @case('bike') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                                    @case('train') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 @break
                                                    @case('bus') bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300 @break
                                                    @case('car') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                    @case('motorcycle') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch
                                            ">
                                                {{ $segment->transport_mode->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ number_format($segment->distance_km, 2) }} km</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ $segment->duration_formatted }}</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ $segment->avg_speed_kmh ? number_format($segment->avg_speed_kmh, 1) . ' km/h' : '-' }}</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ $segment->max_speed_kmh ? number_format($segment->max_speed_kmh, 1) . ' km/h' : '-' }}</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">
                                            @if($segment->generates_credits)
                                                {{ number_format($segment->credits_earned ?? 0, 2) }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($segment->status === 'valid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                @elseif($segment->status === 'invalid') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @endif
                                            ">
                                                {{ ucfirst($segment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <div class="flex justify-end items-center space-x-1">
                                                @if($segment->isPending())
                                                    <button wire:click="quickValidateSegment({{ $segment->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Valida">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <button wire:click="openSegmentModal({{ $segment->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Modifica stato">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Storico Validazioni --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Storico Validazioni ({{ $validationLogs->count() }})
                    </h3>
                    @if($validationLogs->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Nessun cambio di stato registrato per questa traccia.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transizione</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Admin</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Motivazione</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($validationLogs as $log)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ local_dt($log->created_at, 'd/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->previous_status->badgeClasses() }}">
                                                    {{ $log->previous_status->label() }}
                                                </span>
                                                <span class="mx-1 text-gray-400">&rarr;</span>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->new_status->badgeClasses() }}">
                                                    {{ $log->new_status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium
                                                @if($log->credits_delta > 0) text-green-600 dark:text-green-400
                                                @elseif($log->credits_delta < 0) text-red-600 dark:text-red-400
                                                @else text-gray-400 dark:text-gray-500
                                                @endif">
                                                @if($log->credits_delta > 0)+@endif{{ number_format($log->credits_delta, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->changedBy?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $log->reason ?? '—' }}
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

    {{-- Modal Validazione Traccia --}}
    <x-dialog-modal wire:model.live="showValidateModal" maxWidth="md">
        <x-slot name="title">Valida/Invalida Traccia</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Azione" />
                    <div class="mt-2 space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="validationAction" value="valid" class="form-radio text-green-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Valida traccia</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="validationAction" value="invalid" class="form-radio text-red-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Invalida traccia</span>
                        </label>
                    </div>
                </div>
                @if($validationAction === 'invalid')
                    <div>
                        <x-label for="rejectionReason" value="Motivazione" />
                        <textarea wire:model="rejectionReason" id="rejectionReason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci la motivazione..."></textarea>
                        <x-input-error for="rejectionReason" class="mt-2" />
                    </div>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showValidateModal', false)">Annulla</x-secondary-button>
            <x-button wire:click="validateTrack" class="ml-3 {{ $validationAction === 'invalid' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                {{ $validationAction === 'valid' ? 'Valida' : 'Invalida' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Validazione Segmento --}}
    <x-dialog-modal wire:model.live="showSegmentModal" maxWidth="md">
        <x-slot name="title">Valida/Invalida Segmento</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Azione" />
                    <div class="mt-2 space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="segmentAction" value="valid" class="form-radio text-green-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Valida segmento</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="segmentAction" value="invalid" class="form-radio text-red-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Invalida segmento</span>
                        </label>
                    </div>
                </div>
                @if($segmentAction === 'invalid')
                    <div>
                        <x-label for="segmentRejectionReason" value="Motivazione" />
                        <textarea wire:model="segmentRejectionReason" id="segmentRejectionReason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci la motivazione..."></textarea>
                        <x-input-error for="segmentRejectionReason" class="mt-2" />
                    </div>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showSegmentModal', false)">Annulla</x-secondary-button>
            <x-button wire:click="validateSegment" class="ml-3 {{ $segmentAction === 'invalid' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                {{ $segmentAction === 'valid' ? 'Valida' : 'Invalida' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Test Validazione --}}
    <x-dialog-modal wire:model.live="showTestModal" maxWidth="3xl">
        <x-slot name="title">Test Validazione (Dry Run)</x-slot>
        <x-slot name="content">
            @if(!$testResult)
                <div class="text-center py-6">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Questo test eseguira l'algoritmo di validazione senza salvare i risultati.</p>
                    <x-button wire:click="runValidationTest" wire:loading.attr="disabled" class="bg-purple-600 hover:bg-purple-700">
                        <span wire:loading wire:target="runValidationTest">Elaborazione...</span>
                        <span wire:loading.remove wire:target="runValidationTest">Esegui Test</span>
                    </x-button>
                </div>
            @else
                <div class="space-y-4">
                    <div class="p-3 rounded-lg {{ $testResult['is_valid'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                        <p class="font-semibold {{ $testResult['is_valid'] ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400' }}">
                            Risultato: {{ $testResult['is_valid'] ? 'VALIDA' : 'NON VALIDA' }}
                        </p>
                        @if(!empty($testResult['rejection_reason']))
                            <p class="text-sm mt-1 {{ $testResult['is_valid'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $testResult['rejection_reason'] }}
                            </p>
                        @endif
                    </div>

                    @if(!empty($testResult['warnings']))
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <p class="font-semibold text-yellow-700 dark:text-yellow-400 text-sm">Avvisi:</p>
                            <ul class="list-disc list-inside text-sm text-yellow-600 dark:text-yellow-400 mt-1">
                                @foreach($testResult['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($testResult['segments_details']))
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm mb-2">Dettaglio Segmenti:</h4>
                            <div class="space-y-2">
                                @foreach($testResult['segments_details'] as $detail)
                                    <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded text-xs">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                Segmento {{ $detail['sequence'] ?? $loop->iteration }} - {{ $detail['transport_mode'] ?? 'N/D' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full {{ ($detail['is_valid'] ?? false) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                                {{ ($detail['is_valid'] ?? false) ? 'Valido' : 'Non valido' }}
                                            </span>
                                        </div>
                                        @if(!empty($detail['rejection_reason']))
                                            <p class="text-red-600 dark:text-red-400 mt-1">{{ $detail['rejection_reason'] }}</p>
                                        @endif
                                        @if(!empty($detail['credits']))
                                            <p class="text-green-600 dark:text-green-400 mt-1">Crediti: {{ $detail['credits'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="closeTestModal">Chiudi</x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Leaflet Map Script --}}
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            /* Isola lo stacking context di Leaflet per non coprire i modal Jetstream (z-50). */
            #track-map { position: relative; z-index: 0; }
        </style>
    @endassets

    @script
    <script>
        const mapData = @json($mapData);

        if (mapData.segments.length > 0) {
            const map = L.map('track-map');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Aggiungi segmenti
            mapData.segments.forEach(segment => {
                if (segment.polyline && segment.polyline.length > 0) {
                    L.polyline(segment.polyline, {
                        color: segment.color,
                        weight: 4,
                        opacity: 0.8
                    }).addTo(map).bindPopup(
                        `<strong>${segment.transport_mode_label}</strong><br>` +
                        `Distanza: ${segment.distance_km} km<br>` +
                        `Durata: ${segment.duration || '-'}<br>` +
                        `Vel. media: ${segment.avg_speed_kmh ? segment.avg_speed_kmh + ' km/h' : '-'}`
                    );
                }
            });

            // Marker partenza/arrivo
            if (mapData.start[0] && mapData.start[1]) {
                L.marker(mapData.start).addTo(map).bindPopup('Partenza');
            }
            if (mapData.end[0] && mapData.end[1]) {
                L.marker(mapData.end).addTo(map).bindPopup('Arrivo');
            }

            // Fit bounds
            if (mapData.bounds) {
                map.fitBounds([
                    [mapData.bounds.south, mapData.bounds.west],
                    [mapData.bounds.north, mapData.bounds.east]
                ], { padding: [20, 20] });
            } else {
                map.setView(mapData.center, mapData.zoom);
            }
        } else {
            document.getElementById('track-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Nessun dato mappa disponibile</div>';
        }
    </script>
    @endscript
</div>
