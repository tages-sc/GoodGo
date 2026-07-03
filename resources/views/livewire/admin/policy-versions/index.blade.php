<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Message --}}
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
                            Gestione Privacy Policy e Terms of Service
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuova Policy
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex gap-4 mb-6">
                        <div>
                            <select wire:model.live="filterType" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti i tipi</option>
                                <option value="privacy">Privacy Policy</option>
                                <option value="terms">Terms of Service</option>
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="filterStatus" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti gli stati</option>
                                <option value="draft">Bozza</option>
                                <option value="published">Pubblicato</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tabella --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Versione</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Titolo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pubblicata il</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($policies as $policy)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $policy->type->value === 'privacy' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' }}">
                                                {{ $policy->type->value === 'privacy' ? 'Privacy' : 'Terms' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $policy->version }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $policy->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $policy->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' }}">
                                                {{ $policy->status === 'published' ? 'Pubblicato' : 'Bozza' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ local_dt($policy->published_at, 'd/m/Y H:i') ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($policy->status === 'draft')
                                                <button wire:click="publish({{ $policy->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3">
                                                    Pubblica
                                                </button>
                                            @endif
                                            <button wire:click="edit({{ $policy->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                Modifica
                                            </button>
                                            <button wire:click="confirmDelete({{ $policy->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Elimina
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessuna policy trovata. Crea la prima!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $policies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Policy' : 'Nuova Policy' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="type" value="Tipo" />
                        <select wire:model="type" id="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="privacy">Privacy Policy</option>
                            <option value="terms">Terms of Service</option>
                        </select>
                        <x-input-error for="type" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="version" value="Versione" />
                        <x-input wire:model="version" id="version" type="text" class="mt-1 block w-full" placeholder="es. 1.0, 2.1" />
                        <x-input-error for="version" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-label for="title" value="Titolo" />
                    <x-input wire:model="title" id="title" type="text" class="mt-1 block w-full" placeholder="es. Privacy Policy GoodGo" />
                    <x-input-error for="title" class="mt-2" />
                </div>

                <div>
                    <x-label for="content" value="Contenuto" />
                    <textarea wire:model="content" id="content" rows="10" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci il contenuto della policy (supporta Markdown)"></textarea>
                    <x-input-error for="content" class="mt-2" />
                </div>

                <div>
                    <x-label for="status" value="Stato" />
                    <select wire:model="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="draft">Bozza</option>
                        <option value="published">Pubblicato</option>
                    </select>
                    <x-input-error for="status" class="mt-2" />
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
            Elimina Policy
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questa policy? Questa azione non puo essere annullata.
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
