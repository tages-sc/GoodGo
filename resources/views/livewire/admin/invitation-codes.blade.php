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
                            Tutti i Codici Invito
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuovo Codice
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome o codice..." class="w-full" />
                        </div>
                        <div class="min-w-[200px]">
                            <select wire:model.live="filterEnte" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Tutti gli enti</option>
                                @foreach($enti as $ente)
                                    <option value="{{ $ente->id }}">{{ $ente->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tabella --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Codice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scadenza</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utilizzi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($invitationCodes as $invitationCode)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $invitationCode->ente?->name ?? '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $invitationCode->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono text-gray-900 dark:text-gray-100">
                                                {{ $invitationCode->code }}
                                            </code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm {{ $invitationCode->expires_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                                                {{ $invitationCode->expires_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($invitationCode->isValid())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Attivo
                                                </span>
                                            @elseif(!$invitationCode->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Disattivato
                                                </span>
                                            @elseif($invitationCode->expires_at->isPast())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    Scaduto
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    Limite raggiunto
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $invitationCode->uses_count }}{{ $invitationCode->max_uses > 0 ? ' / ' . $invitationCode->max_uses : '' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <button wire:click="toggleActive({{ $invitationCode->id }})" class="{{ $invitationCode->is_active ? 'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400' : 'text-green-600 hover:text-green-900 dark:text-green-400' }}" title="{{ $invitationCode->is_active ? 'Disattiva' : 'Attiva' }}">
                                                    @if($invitationCode->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                                <button wire:click="edit({{ $invitationCode->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifica">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="confirmDelete({{ $invitationCode->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Elimina">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessun codice invito presente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $invitationCodes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="lg">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Codice Invito' : 'Nuovo Codice Invito' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="ente_id" value="Ente *" />
                    <select wire:model="ente_id" id="ente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Seleziona ente...</option>
                        @foreach($enti as $ente)
                            <option value="{{ $ente->id }}">{{ $ente->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="ente_id" class="mt-2" />
                </div>

                <div>
                    <x-label for="name" value="Nome *" />
                    <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="Es: Campagna Primavera 2026" />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <x-label for="code" value="Codice (6 cifre) *" />
                    <div class="mt-1 flex items-center space-x-2">
                        <x-input wire:model="code" id="code" type="text" class="flex-1 font-mono" maxlength="6" placeholder="000000" />
                        <button wire:click="generateCode" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Genera
                        </button>
                    </div>
                    <x-input-error for="code" class="mt-2" />
                </div>

                <div>
                    <x-label for="expires_at" value="Data di Scadenza *" />
                    <x-input wire:model="expires_at" id="expires_at" type="date" class="mt-1 block w-full" />
                    <x-input-error for="expires_at" class="mt-2" />
                </div>

                <div>
                    <x-label for="max_uses" value="Limite Utilizzi (0 = illimitato)" />
                    <x-input wire:model="max_uses" id="max_uses" type="number" min="0" class="mt-1 block w-full" />
                    <x-input-error for="max_uses" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <x-checkbox wire:model="is_active" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Codice attivo</span>
                    </label>
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
            Elimina Codice Invito
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questo codice invito? Questa azione non puo essere annullata.
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
</div>
