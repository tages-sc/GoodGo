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
                            Gestione Organizzatori
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuovo Organizzatore
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome o email..." class="w-full" />
                        </div>
                        <div>
                            <select wire:model.live="filterEnte" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Organizzatore</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipologia</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gare</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($organizers as $organizer)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center text-white font-bold" style="background-color: {{ $organizer->enteProfile?->colore ?? '#4CAF50' }}">
                                                    {{ strtoupper(substr($organizer->name, 0, 2)) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $organizer->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $organizer->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $organizer->parentEnte?->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $organizer->enteProfile?->tipologia ? ucfirst($organizer->enteProfile->tipologia) : '-' }}
                                            </span>
                                            @if($organizer->enteProfile?->location)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $organizer->enteProfile->location }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $organizer->organized_competitions_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <button wire:click="edit({{ $organizer->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifica">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="confirmDelete({{ $organizer->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Elimina">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessun organizzatore trovato.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $organizers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="3xl">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Organizzatore' : 'Nuovo Organizzatore' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                {{-- Credenziali Accesso --}}
                <div class="border-b dark:border-gray-700 pb-4 mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Credenziali Accesso</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="name" value="Nome Organizzatore *" />
                            <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="email" value="Email *" />
                            <x-input wire:model="email" id="email" type="email" class="mt-1 block w-full" />
                            <x-input-error for="email" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="password" value="{{ $editingId ? 'Nuova Password (lascia vuoto per non modificare)' : 'Password *' }}" />
                            <x-input wire:model="password" id="password" type="password" class="mt-1 block w-full" />
                            <x-input-error for="password" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="parent_ente_id" value="Ente di appartenenza *" />
                            <select wire:model="parent_ente_id" id="parent_ente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Seleziona ente...</option>
                                @foreach($enti as $ente)
                                    <option value="{{ $ente->id }}">{{ $ente->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="parent_ente_id" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Info Organizzatore --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="tipologia" value="Tipologia" />
                        <select wire:model="tipologia" id="tipologia" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Seleziona tipologia...</option>
                            <option value="comune">Comune</option>
                            <option value="azienda">Azienda</option>
                        </select>
                        <x-input-error for="tipologia" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="location" value="Localita" />
                        <x-input wire:model="location" id="location" type="text" class="mt-1 block w-full" placeholder="Es: Pisa, Toscana..." />
                        <x-input-error for="location" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-label for="descrizione" value="Descrizione" />
                    <textarea wire:model="descrizione" id="descrizione" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error for="descrizione" class="mt-2" />
                </div>

                {{-- Impostazioni --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Impostazioni</h4>
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <x-checkbox wire:model="iscrizione_moderata" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Iscrizioni moderate (richiede approvazione)</span>
                        </label>
                    </div>
                </div>

                {{-- Links e Social --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Links e Social</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="website" value="Sito Web" />
                            <x-input wire:model="website" id="website" type="url" class="mt-1 block w-full" placeholder="https://..." />
                            <x-input-error for="website" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="instagram_url" value="Instagram" />
                            <x-input wire:model="instagram_url" id="instagram_url" type="url" class="mt-1 block w-full" placeholder="https://instagram.com/..." />
                            <x-input-error for="instagram_url" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="facebook_url" value="Facebook" />
                            <x-input wire:model="facebook_url" id="facebook_url" type="url" class="mt-1 block w-full" placeholder="https://facebook.com/..." />
                            <x-input-error for="facebook_url" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="linkedin_url" value="LinkedIn" />
                            <x-input wire:model="linkedin_url" id="linkedin_url" type="url" class="mt-1 block w-full" placeholder="https://linkedin.com/..." />
                            <x-input-error for="linkedin_url" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="twitter_url" value="Twitter/X" />
                            <x-input wire:model="twitter_url" id="twitter_url" type="url" class="mt-1 block w-full" placeholder="https://twitter.com/..." />
                            <x-input-error for="twitter_url" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Personalizzazione --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Personalizzazione</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-label for="colore" value="Colore" />
                            <div class="mt-1 flex items-center space-x-2">
                                <input wire:model="colore" id="colore" type="color" class="h-10 w-14 rounded border-gray-300 dark:border-gray-700" />
                                <x-input wire:model="colore" type="text" class="flex-1" placeholder="#4CAF50" />
                            </div>
                            <x-input-error for="colore" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="logo" value="Logo" />
                            <input wire:model="logo" id="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="logo" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="banner" value="Banner" />
                            <input wire:model="banner" id="banner" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                            <x-input-error for="banner" class="mt-2" />
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
            Elimina Organizzatore
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questo organizzatore? Questa azione non puo essere annullata.
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
