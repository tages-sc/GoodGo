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
                            Gestione Enti
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuovo Ente
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome, email, citta..." class="w-full" />
                        </div>
                        <div>
                            <select wire:model.live="filterType" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti i tipi</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="filterActive" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti gli stati</option>
                                <option value="1">Attivi</option>
                                <option value="0">Disattivati</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tabella --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Citta</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Iscritti</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Moderato</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($organizations as $org)
                                    <tr class="{{ $org->is_default ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white font-bold" style="background-color: {{ $org->color }}">
                                                    {{ strtoupper(substr($org->name, 0, 2)) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $org->name }}
                                                        @if($org->is_default)
                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Default</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $org->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $types[$org->type] ?? $org->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $org->city ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <a href="{{ route('admin.organizations.members', $org) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                                <span class="text-sm font-medium">{{ $org->approved_users_count }}</span>
                                                @if($org->pending_users_count > 0)
                                                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 rounded-full">+{{ $org->pending_users_count }}</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($org->moderated_subscription)
                                                <span class="text-yellow-600 dark:text-yellow-400">
                                                    <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                                </span>
                                            @else
                                                <span class="text-green-600 dark:text-green-400">
                                                    <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="toggleActive({{ $org->id }})" class="focus:outline-none" {{ $org->is_default ? 'disabled' : '' }}>
                                                @if($org->is_active)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Attivo</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Disattivo</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.organizations.members', $org) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                                Iscritti
                                            </a>
                                            <button wire:click="edit({{ $org->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                Modifica
                                            </button>
                                            @unless($org->is_default)
                                                <button wire:click="confirmDelete({{ $org->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Elimina
                                                </button>
                                            @endunless
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessun ente trovato.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $organizations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="2xl">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Ente' : 'Nuovo Ente' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                {{-- Info Base --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="name" value="Nome *" />
                        <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="type" value="Tipo *" />
                        <select wire:model="type" id="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="type" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-label for="description" value="Descrizione" />
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="color" value="Colore" />
                        <div class="flex items-center gap-2 mt-1">
                            <input wire:model="color" id="color" type="color" class="h-10 w-20 rounded cursor-pointer" />
                            <x-input wire:model="color" type="text" class="flex-1" />
                        </div>
                        <x-input-error for="color" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="logo" value="Logo" />
                        <input wire:model="logo" id="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                        <x-input-error for="logo" class="mt-2" />
                    </div>
                </div>

                {{-- Indirizzo --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Indirizzo</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <x-label for="address" value="Via/Piazza" />
                            <x-input wire:model="address" id="address" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-label for="city" value="Citta" />
                            <x-input wire:model="city" id="city" type="text" class="mt-1 block w-full" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <x-label for="province" value="Prov." />
                                <x-input wire:model="province" id="province" type="text" class="mt-1 block w-full" maxlength="2" />
                            </div>
                            <div>
                                <x-label for="postal_code" value="CAP" />
                                <x-input wire:model="postal_code" id="postal_code" type="text" class="mt-1 block w-full" maxlength="5" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contatti --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Contatti</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="phone" value="Telefono" />
                            <x-input wire:model="phone" id="phone" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-label for="email" value="Email" />
                            <x-input wire:model="email" id="email" type="email" class="mt-1 block w-full" />
                            <x-input-error for="email" class="mt-2" />
                        </div>
                        <div class="col-span-2">
                            <x-label for="website" value="Sito Web" />
                            <x-input wire:model="website" id="website" type="url" class="mt-1 block w-full" placeholder="https://" />
                            <x-input-error for="website" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- Social --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Social</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="facebook_url" value="Facebook" />
                            <x-input wire:model="facebook_url" id="facebook_url" type="url" class="mt-1 block w-full" placeholder="https://facebook.com/..." />
                        </div>
                        <div>
                            <x-label for="instagram_url" value="Instagram" />
                            <x-input wire:model="instagram_url" id="instagram_url" type="url" class="mt-1 block w-full" placeholder="https://instagram.com/..." />
                        </div>
                        <div>
                            <x-label for="twitter_url" value="Twitter/X" />
                            <x-input wire:model="twitter_url" id="twitter_url" type="url" class="mt-1 block w-full" placeholder="https://twitter.com/..." />
                        </div>
                        <div>
                            <x-label for="linkedin_url" value="LinkedIn" />
                            <x-input wire:model="linkedin_url" id="linkedin_url" type="url" class="mt-1 block w-full" placeholder="https://linkedin.com/..." />
                        </div>
                    </div>
                </div>

                {{-- Impostazioni --}}
                <div class="border-t dark:border-gray-700 pt-4 mt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Impostazioni</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <x-checkbox wire:model="moderated_subscription" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Iscrizione moderata (richiede approvazione)</span>
                        </label>
                        <label class="flex items-center">
                            <x-checkbox wire:model="is_active" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ente attivo</span>
                        </label>
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
            Elimina Ente
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questo ente? Tutti gli utenti iscritti verranno rimossi. Questa azione non puo essere annullata.
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
