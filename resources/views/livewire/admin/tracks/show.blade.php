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
                    <a href="{{ route('admin.tracks.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
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
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Testa Validazione
                    </x-secondary-button>

                    <x-button wire:click="openValidateModal" class="{{ $track->canBeValidated() ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ $track->canBeValidated() ? 'Valida' : 'Modifica Stato' }}
                    </x-button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Mappa --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Mappa Percorso</h3>
                            <div wire:ignore id="track-map" class="h-[500px] rounded-lg border border-gray-200 dark:border-gray-700" style="height: 500px; min-height: 500px;"></div>

                            {{-- Legenda --}}
                            <div class="mt-4 flex flex-wrap gap-3">
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-green-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Piedi</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-blue-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Bici</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-amber-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Treno</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-violet-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Bus</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-red-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Auto</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-4 h-1 bg-orange-500 mr-2"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Moto</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Panel --}}
                <div class="space-y-6">
                    {{-- Info Traccia --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informazioni</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Utente</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->user->name }}</dd>
                                </div>
                                @if($track->competition)
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Gara</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->competition->name }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Data</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ local_dt($track->started_at, 'd/m/Y H:i') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Durata</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->duration_formatted }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Distanza Totale</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($track->total_distance_km, 2) }} km</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Distanza Valida</dt>
                                    <dd class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($track->valid_distance_km, 2) }} km</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Modalita Principale</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->primary_transport_mode?->label() ?? '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Multimodale</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->is_multimodal ? 'Si' : 'No' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Punti GPS</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($track->points_count) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Velocita Media</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->avg_speed_kmh ? number_format($track->avg_speed_kmh, 1) . ' km/h' : '-' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Accuratezza Media</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->avg_accuracy_meters ? number_format($track->avg_accuracy_meters, 1) . ' m' : '-' }}</dd>
                                </div>
                                @if($track->notes)
                                    <div class="pt-3 border-t dark:border-gray-700">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Note Utente</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $track->notes }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    {{-- Crediti e Emissioni --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Crediti & Emissioni</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Crediti Guadagnati</dt>
                                    <dd class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($track->credits_earned, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">CO2 Risparmiata</dt>
                                    <dd class="text-sm font-medium text-green-600 dark:text-green-400">{{ number_format($track->co2_saved_grams, 0) }} g</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Calorie Bruciate</dt>
                                    <dd class="text-sm font-medium text-orange-600 dark:text-orange-400">{{ number_format($track->calories_burned, 0) }} kcal</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Validazione --}}
                    @if($track->validated_at)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Validazione</h3>
                                <dl class="space-y-3">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Data</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ local_dt($track->validated_at, 'd/m/Y H:i') }}</dd>
                                    </div>
                                    @if($track->validator)
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Validatore</dt>
                                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $track->validator->name }}</dd>
                                        </div>
                                    @endif
                                    @if($track->rejection_reason)
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Motivazione Rifiuto</dt>
                                            <dd class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded">{{ $track->rejection_reason }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Segmenti --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Segmenti ({{ $track->segments->count() }})
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modalita</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distanza</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durata</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vel. Media</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Punti</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($track->segments as $segment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $segment->sequence + 1 }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @switch($segment->transport_mode->value)
                                                    @case('walk') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                    @case('bike') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                                    @case('train') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 @break
                                                    @case('bus') bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300 @break
                                                    @case('car') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                    @case('motorcycle') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 @break
                                                    @default bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                                                @endswitch
                                            ">
                                                {{ $segment->transport_mode->label() }}
                                            </span>
                                            @if(!$segment->generates_credits)
                                                <span class="ml-1 text-xs text-gray-400" title="Non genera crediti">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($segment->distance_km, 2) }} km
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            {{ $segment->duration_formatted }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            {{ $segment->avg_speed_kmh ? number_format($segment->avg_speed_kmh, 1) . ' km/h' : '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($segment->points_count) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ number_format($segment->credits_earned, 2) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @switch($segment->status)
                                                    @case('valid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                    @case('invalid') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                    @default bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @endswitch
                                            ">
                                                {{ ucfirst($segment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            @if($segment->status === 'pending')
                                                <button wire:click="quickValidateSegment({{ $segment->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Valida">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            <button wire:click="openSegmentModal({{ $segment->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 ml-2" title="Modifica Stato">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    {{-- Dettaglio motivazione + controlli per segmenti non validi --}}
                                    @if($segment->status !== 'valid' && ($segment->rejection_reason || !empty($segment->validation_details)))
                                        <tr class="bg-red-50/40 dark:bg-red-900/10">
                                            <td colspan="9" class="px-4 pb-3 pt-0">
                                                @if($segment->rejection_reason)
                                                    <p class="text-xs text-red-600 dark:text-red-400">
                                                        <strong>Motivo:</strong> {{ $segment->rejection_reason }}
                                                    </p>
                                                @endif
                                                @if(!empty($segment->validation_details))
                                                    <div class="mt-2">
                                                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Controlli eseguiti:</p>
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($segment->validation_details as $checkName => $checkResult)
                                                                @php($passed = is_array($checkResult) ? ($checkResult['passed'] ?? true) : (bool) $checkResult)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $passed ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400' }}">
                                                                    @if($passed)
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                    @else
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    @endif
                                                                    {{ str_replace('_', ' ', ucfirst($checkName)) }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
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
        <x-slot name="title">
            Valida/Invalida Traccia
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Azione" />
                    <div class="mt-2 space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="validationAction" value="valid" class="form-radio text-green-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Valida traccia (e tutti i segmenti pending)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="validationAction" value="invalid" class="form-radio text-red-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Invalida traccia (e tutti i segmenti)</span>
                        </label>
                    </div>
                </div>

                @if($validationAction === 'invalid')
                    <div>
                        <x-label for="rejectionReason" value="Motivazione" />
                        <textarea wire:model="rejectionReason" id="rejectionReason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci la motivazione dell'invalidazione..."></textarea>
                        <x-input-error for="rejectionReason" class="mt-2" />
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showValidateModal', false)">
                Annulla
            </x-secondary-button>
            <x-button wire:click="validateTrack" class="ml-3 {{ $validationAction === 'invalid' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                {{ $validationAction === 'valid' ? 'Valida' : 'Invalida' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Validazione Segmento --}}
    <x-dialog-modal wire:model.live="showSegmentModal" maxWidth="md">
        <x-slot name="title">
            Modifica Stato Segmento
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label value="Stato" />
                    <div class="mt-2 space-y-2">
                        <label class="flex items-center">
                            <input type="radio" wire:model="segmentAction" value="valid" class="form-radio text-green-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Valido</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" wire:model="segmentAction" value="invalid" class="form-radio text-red-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Non valido</span>
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
            <x-secondary-button wire:click="$set('showSegmentModal', false)">
                Annulla
            </x-secondary-button>
            <x-button wire:click="validateSegment" class="ml-3">
                Salva
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Test Validazione (Dry Run) --}}
    <x-dialog-modal wire:model.live="showTestModal" maxWidth="5xl">
        <x-slot name="title">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Test Validazione (Dry Run)
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                {{-- Pulsante per avviare il test --}}
                @if(!$testResult)
                    <div class="text-center py-8">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Questo test esegue l'algoritmo di validazione <strong>senza salvare</strong> i risultati nel database.
                            <br>Utile per verificare il funzionamento dei controlli prima di procedere.
                        </p>
                        <x-button wire:click="runValidationTest" wire:loading.attr="disabled" class="bg-purple-600 hover:bg-purple-700">
                            <span wire:loading.remove wire:target="runValidationTest">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Avvia Test
                            </span>
                            <span wire:loading wire:target="runValidationTest">
                                <svg class="animate-spin w-4 h-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Esecuzione in corso...
                            </span>
                        </x-button>
                    </div>
                @else
                    {{-- Risultato del test --}}
                    <div class="space-y-6">
                        {{-- Riepilogo Principale --}}
                        <div class="p-4 rounded-lg {{ $testResult['is_valid'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if($testResult['is_valid'])
                                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="ml-3 text-lg font-semibold text-green-800 dark:text-green-300">Traccia VALIDA</span>
                                    @else
                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="ml-3 text-lg font-semibold text-red-800 dark:text-red-300">Traccia NON VALIDA</span>
                                    @endif
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                    DRY RUN
                                </span>
                            </div>

                            @if($testResult['rejection_reason'])
                                <p class="mt-2 text-sm text-red-700 dark:text-red-400">
                                    <strong>Motivo:</strong> {{ $testResult['rejection_reason'] }}
                                </p>
                            @endif
                        </div>

                        {{-- Statistiche --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $testResult['segments_validated'] ?? 0 }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Segmenti Validi</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $testResult['segments_invalid'] ?? 0 }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Segmenti Non Validi</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format(($testResult['valid_distance_meters'] ?? 0) / 1000, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Km Validi</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($testResult['credits_earned'] ?? 0, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Crediti</p>
                            </div>
                        </div>

                        {{-- Warning --}}
                        @if(!empty($testResult['warnings']))
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Warning ({{ count($testResult['warnings']) }})
                                </h4>
                                <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-400 space-y-1">
                                    @foreach($testResult['warnings'] as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Dettagli Segmenti --}}
                        @if(!empty($testResult['segments_details']))
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Dettaglio Segmenti</h4>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    @foreach($testResult['segments_details'] as $seg)
                                        <div class="border rounded-lg p-3 {{ $seg['is_valid'] ? 'border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10' : 'border-red-200 dark:border-red-800 bg-red-50/50 dark:bg-red-900/10' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center space-x-3">
                                                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                        #{{ $seg['sequence'] + 1 }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                                        @switch($seg['transport_mode'])
                                                            @case('walk') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                            @case('bike') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                                            @case('train') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 @break
                                                            @case('bus') bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300 @break
                                                            @case('car') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                            @case('motorcycle') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 @break
                                                            @default bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                                                        @endswitch
                                                    ">
                                                        {{ $seg['transport_mode_label'] }}
                                                    </span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $seg['is_valid'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                                    {{ $seg['is_valid'] ? 'VALIDO' : 'NON VALIDO' }}
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                                <div>
                                                    <span class="font-medium">Distanza:</span>
                                                    {{ $seg['distance_km'] }} km
                                                </div>
                                                <div>
                                                    <span class="font-medium">Durata:</span>
                                                    {{ $seg['duration'] }}
                                                </div>
                                                <div>
                                                    <span class="font-medium">Vel. Media:</span>
                                                    {{ $seg['avg_speed_kmh'] ?? '-' }} km/h
                                                </div>
                                                <div>
                                                    <span class="font-medium">Crediti:</span>
                                                    <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($seg['credits_earned'], 2) }}</span>
                                                </div>
                                            </div>

                                            @if(!$seg['is_valid'] && $seg['rejection_reason'])
                                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                                                    <strong>Motivo:</strong> {{ $seg['rejection_reason'] }}
                                                </p>
                                            @endif

                                            {{-- Dettaglio controlli --}}
                                            @if(!empty($seg['validation_checks']))
                                                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Controlli eseguiti:</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($seg['validation_checks'] as $checkName => $checkResult)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ ($checkResult['passed'] ?? true) ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400' }}">
                                                                @if($checkResult['passed'] ?? true)
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                    </svg>
                                                                @endif
                                                                {{ str_replace('_', ' ', ucfirst($checkName)) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Extra: CO2 e Calorie --}}
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">CO2 Risparmiata</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                    {{ number_format(($testResult['co2_saved_grams'] ?? 0), 0) }} g
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Calorie Bruciate</p>
                                <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ number_format(($testResult['calories_burned'] ?? 0), 0) }} kcal
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            @if($testResult)
                <x-secondary-button wire:click="$set('testResult', null)">
                    Ripeti Test
                </x-secondary-button>
            @endif
            <x-secondary-button wire:click="closeTestModal" class="ml-3">
                Chiudi
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Leaflet CSS e JS inline --}}
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            /* Isola lo stacking context di Leaflet (controlli e pane arrivano fino a z-index 1000)
               per evitare che la mappa copra i modal Jetstream (z-50). */
            #track-map { position: relative; z-index: 0; }
        </style>
    @endassets

    @script
    <script>
        const mapData = @json($mapData);

        // Aspetta che il DOM sia pronto
        setTimeout(function() {
            const mapContainer = document.getElementById('track-map');
            if (!mapContainer || typeof L === 'undefined') return;

            // Verifica che center sia valido
            const center = (mapData.center && mapData.center[0] && mapData.center[1])
                ? mapData.center
                : [43.7, 10.4]; // Default: Toscana

            const map = L.map('track-map').setView(center, mapData.zoom || 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            if (mapData.bounds) {
                map.fitBounds([
                    [mapData.bounds.south, mapData.bounds.west],
                    [mapData.bounds.north, mapData.bounds.east]
                ], { padding: [30, 30] });
            }

            if (mapData.start && mapData.start[0] && mapData.start[1]) {
                L.marker(mapData.start, {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div style="width:24px;height:24px;background:#22c55e;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:bold;">P</div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).addTo(map).bindPopup('Partenza');
            }

            if (mapData.end && mapData.end[0] && mapData.end[1]) {
                L.marker(mapData.end, {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div style="width:24px;height:24px;background:#ef4444;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:bold;">A</div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).addTo(map).bindPopup('Arrivo');
            }

            if (mapData.segments) {
                mapData.segments.forEach(function(segment) {
                    if (segment.polyline && segment.polyline.length > 0) {
                        const polyline = L.polyline(segment.polyline, {
                            color: segment.color,
                            weight: 5,
                            opacity: segment.status === 'invalid' ? 0.5 : 1.0
                        }).addTo(map);

                        polyline.bindPopup(
                            '<strong>Segmento ' + (segment.sequence + 1) + '</strong><br>' +
                            '<span style="color: ' + segment.color + '">' + segment.transport_mode_label + '</span><br>' +
                            'Distanza: ' + segment.distance_km + ' km<br>' +
                            'Durata: ' + segment.duration + '<br>' +
                            (segment.avg_speed_kmh ? 'Vel. media: ' + segment.avg_speed_kmh + ' km/h<br>' : '') +
                            'Stato: ' + segment.status + '<br>' +
                            (segment.generates_credits ? '<span style="color:#22c55e">Genera crediti</span>' : '<span style="color:#ef4444">Non genera crediti</span>')
                        );

                        if (segment.status === 'invalid') {
                            polyline.setStyle({ dashArray: '10, 10' });
                        }
                    }
                });
            }

            // Fit ai bounds della traccia
            if (mapData.bounds) {
                map.fitBounds([
                    [mapData.bounds.south, mapData.bounds.west],
                    [mapData.bounds.north, mapData.bounds.east]
                ], { padding: [30, 30] });
            }

            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        }, 100);
    </script>
    @endscript
</div>
