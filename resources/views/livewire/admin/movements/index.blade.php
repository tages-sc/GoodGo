<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Gestione Movimenti e Crediti
                </h2>
                <x-button wire:click="openAdjustModal" class="bg-purple-600 hover:bg-purple-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Rettifica Crediti
                </x-button>
            </div>

            {{-- Statistiche --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Attesa</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Approvati Oggi</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['approved_today'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Crediti Movimentati</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_credits_moved'], 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtri --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <x-label for="search" value="Cerca" />
                            <x-input wire:model.live.debounce.300ms="search" id="search" class="block mt-1 w-full" placeholder="Utente o descrizione..." />
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
                        <div>
                            <x-label for="filterType" value="Tipo" />
                            <select wire:model.live="filterType" id="filterType" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Tutti</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="filterUser" value="Utente" />
                            <select wire:model.live="filterUser" id="filterUser" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Tutti</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="filterPartner" value="Partner" />
                            <select wire:model.live="filterPartner" id="filterPartner" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Tutti</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabella Movimenti --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Crediti</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Partner</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descrizione</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($movements as $movement)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        #{{ $movement->id }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $movement->user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->user->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $movement->type->badgeClasses() }}">
                                            {{ $movement->type->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="text-sm font-bold {{ $movement->type->deductsCredits() ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $movement->type->deductsCredits() ? '-' : '+' }}{{ $movement->formatted_credits }}
                                        </span>
                                        @if($movement->euro_amount)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->formatted_euro }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $movement->partner?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate" title="{{ $movement->description }}">
                                        {{ $movement->description }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $movement->status->badgeClasses() }}">
                                            {{ $movement->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $movement->created_at->format('d/m/Y H:i') }}
                                        @if($movement->processed_at)
                                            <div class="text-xs">Proc: {{ $movement->processed_at->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        @if($movement->canBeProcessed())
                                            <button wire:click="openProcessModal({{ $movement->id }}, 'approve')" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Approva">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            <button wire:click="openProcessModal({{ $movement->id }}, 'reject')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 ml-2" title="Rifiuta">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @else
                                            @if($movement->rejection_reason)
                                                <span class="text-xs text-red-500 dark:text-red-400" title="{{ $movement->rejection_reason }}">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Nessun movimento trovato.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginazione --}}
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Processa Movimento --}}
    <x-dialog-modal wire:model.live="showProcessModal" maxWidth="md">
        <x-slot name="title">
            {{ $processAction === 'approve' ? 'Approva Movimento' : 'Rifiuta Movimento' }}
        </x-slot>

        <x-slot name="content">
            @if($processAction === 'approve')
                <p class="text-gray-600 dark:text-gray-400">
                    Sei sicuro di voler approvare questo movimento? I crediti verranno trasferiti.
                </p>
            @else
                <div>
                    <x-label for="rejectionReason" value="Motivazione del rifiuto" />
                    <textarea wire:model="rejectionReason" id="rejectionReason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci la motivazione..."></textarea>
                    <x-input-error for="rejectionReason" class="mt-2" />
                </div>
            @endif
            <x-input-error for="process" class="mt-2" />
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showProcessModal', false)">
                Annulla
            </x-secondary-button>
            <x-button wire:click="processMovement" class="ml-3 {{ $processAction === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                {{ $processAction === 'approve' ? 'Approva' : 'Rifiuta' }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Rettifica Crediti --}}
    <x-dialog-modal wire:model.live="showAdjustModal" maxWidth="md">
        <x-slot name="title">
            Rettifica Manuale Crediti
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="adjustUserId" value="Utente" />
                    <select wire:model="adjustUserId" id="adjustUserId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Seleziona utente...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) - Saldo: {{ number_format($user->credits, 2) }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="adjustUserId" class="mt-2" />
                </div>

                <div>
                    <x-label for="adjustAmount" value="Importo (positivo = aggiunta, negativo = sottrazione)" />
                    <x-input wire:model="adjustAmount" type="number" step="0.01" id="adjustAmount" class="block mt-1 w-full" placeholder="Es: 10.00 oppure -5.00" />
                    <x-input-error for="adjustAmount" class="mt-2" />
                </div>

                <div>
                    <x-label for="adjustReason" value="Motivazione" />
                    <textarea wire:model="adjustReason" id="adjustReason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci la motivazione della rettifica..."></textarea>
                    <x-input-error for="adjustReason" class="mt-2" />
                </div>

                <x-input-error for="adjust" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAdjustModal', false)">
                Annulla
            </x-secondary-button>
            <x-button wire:click="createAdjustment" class="ml-3 bg-purple-600 hover:bg-purple-700">
                Applica Rettifica
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
