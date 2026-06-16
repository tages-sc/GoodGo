<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Lista Occupazioni
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuova Occupazione
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome..." class="w-full" />
                        </div>
                    </div>

                    {{-- Tabella con Drag & Drop --}}
                    <div class="overflow-x-auto"
                         x-data="{
                             draggingId: null,
                             dragOverId: null,
                             handleDragStart(e, id) {
                                 this.draggingId = id;
                                 e.dataTransfer.effectAllowed = 'move';
                                 e.target.closest('tr').classList.add('opacity-50');
                             },
                             handleDragEnd(e) {
                                 e.target.closest('tr').classList.remove('opacity-50');
                                 this.draggingId = null;
                                 this.dragOverId = null;
                             },
                             handleDragOver(e, id) {
                                 e.preventDefault();
                                 e.dataTransfer.dropEffect = 'move';
                                 this.dragOverId = id;
                             },
                             handleDrop(e, targetId) {
                                 e.preventDefault();
                                 this.dragOverId = null;
                                 if (this.draggingId === targetId) return;
                                 const rows = [...document.querySelectorAll('tbody tr[data-id]')];
                                 const orderedIds = rows.map(r => parseInt(r.dataset.id));
                                 const fromIndex = orderedIds.indexOf(this.draggingId);
                                 const toIndex = orderedIds.indexOf(targetId);
                                 orderedIds.splice(fromIndex, 1);
                                 orderedIds.splice(toIndex, 0, this.draggingId);
                                 $wire.updateOrder(orderedIds);
                             }
                         }">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12"></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">Ordine</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($occupations as $occupation)
                                    <tr data-id="{{ $occupation->id }}"
                                        draggable="true"
                                        x-on:dragstart="handleDragStart($event, {{ $occupation->id }})"
                                        x-on:dragend="handleDragEnd($event)"
                                        x-on:dragover="handleDragOver($event, {{ $occupation->id }})"
                                        x-on:drop="handleDrop($event, {{ $occupation->id }})"
                                        :class="dragOverId === {{ $occupation->id }} && draggingId !== {{ $occupation->id }} ? 'border-t-2 border-indigo-500' : ''"
                                        class="transition-colors cursor-grab active:cursor-grabbing">
                                        <td class="px-3 py-4 whitespace-nowrap text-center">
                                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                            </svg>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $occupation->sort_order }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $occupation->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($occupation->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    Attiva
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                    Disattivata
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <button wire:click="toggleActive({{ $occupation->id }})" class="{{ $occupation->is_active ? 'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400' : 'text-green-600 hover:text-green-900 dark:text-green-400' }}" title="{{ $occupation->is_active ? 'Disattiva' : 'Attiva' }}">
                                                    @if($occupation->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                                <button wire:click="edit({{ $occupation->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Modifica">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button wire:click="confirmDelete({{ $occupation->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Elimina">
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
                                            Nessuna occupazione presente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $occupations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="md">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Occupazione' : 'Nuova Occupazione' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="Nome *" />
                    <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="Es: Impiegato, Studente..." />
                    <x-input-error for="name" class="mt-2" />
                </div>

                <div>
                    <x-label for="sort_order" value="Ordine" />
                    <x-input wire:model="sort_order" id="sort_order" type="number" min="0" class="mt-1 block w-full" />
                    <x-input-error for="sort_order" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <x-checkbox wire:model="is_active" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Attiva</span>
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
            Elimina Occupazione
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questa occupazione? Questa azione non puo essere annullata.
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
