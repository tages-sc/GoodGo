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
                        <div class="flex items-center space-x-4">
                            <a href="{{ route($backRouteName) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Tracce - {{ $competition->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $competition->start_date?->format('d/m/Y') }} - {{ $competition->end_date?->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per session ID o utente..." class="w-full" />
                        </div>
                        <div>
                            <select wire:model.live="filterStatus" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti gli stati</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="filterTransportMode" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutte le modalita</option>
                                @foreach($transportModes as $value => $label)
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modalita</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Distanza</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($tracks as $track)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                #{{ $track->id }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                                {{ Str::limit($track->session_id, 12) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $track->user->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $track->user->email }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ local_dt($track->started_at, 'd/m/Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ local_dt($track->started_at, 'H:i') }} - {{ local_dt($track->ended_at, 'H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            @if($track->primary_transport_mode)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @switch($track->primary_transport_mode->value)
                                                        @case('walk') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                        @case('bike') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                                        @case('train') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 @break
                                                        @case('bus') bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300 @break
                                                        @case('car') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                        @case('motorcycle') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 @break
                                                        @default bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300
                                                    @endswitch
                                                ">
                                                    {{ $track->primary_transport_mode->label() }}
                                                </span>
                                                @if($track->is_multimodal)
                                                    <span class="ml-1 text-xs text-gray-500">(+{{ $track->segments->count() - 1 }})</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ number_format($track->total_distance_km, 2) }} km
                                            </div>
                                            @if($track->valid_distance_meters > 0 && $track->valid_distance_meters != $track->total_distance_meters)
                                                <div class="text-xs text-green-600 dark:text-green-400">
                                                    ({{ number_format($track->valid_distance_km, 2) }} validi)
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ number_format($track->credits_earned ?? 0, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $track->status->badgeClasses() }}">
                                                {{ $track->status->label() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                {{-- View --}}
                                                <a href="{{ route($showRouteName, [$competition, $track]) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Dettaglio">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>

                                                {{-- Quick Validate (solo se pending/processing) --}}
                                                @if($track->canBeValidated())
                                                    <button wire:click="quickValidate({{ $track->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Valida">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                @endif

                                                {{-- Modifica Stato (sempre disponibile) --}}
                                                <button wire:click="openValidateModal({{ $track->id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Modifica stato">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            <p class="mt-2">Nessuna traccia trovata per questa gara.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $tracks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Validazione --}}
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
            <x-button wire:click="validate_track" class="ml-3 {{ $validationAction === 'invalid' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                {{ $validationAction === 'valid' ? 'Valida' : 'Invalida' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
