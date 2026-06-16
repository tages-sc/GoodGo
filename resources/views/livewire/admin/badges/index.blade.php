<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Message --}}
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
                            Gestione Badge Gamification
                        </h3>
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuovo Badge
                        </button>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex gap-4 mb-6">
                        <div>
                            <select wire:model.live="filterCategory" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutte le categorie</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Badge</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoria</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stelle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Soglia</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vincitori</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($badges as $badge)
                                    <tr data-id="{{ $badge->id }}"
                                        draggable="true"
                                        x-on:dragstart="handleDragStart($event, {{ $badge->id }})"
                                        x-on:dragend="handleDragEnd($event)"
                                        x-on:dragover="handleDragOver($event, {{ $badge->id }})"
                                        x-on:drop="handleDrop($event, {{ $badge->id }})"
                                        :class="dragOverId === {{ $badge->id }} && draggingId !== {{ $badge->id }} ? 'border-t-2 border-indigo-500' : ''"
                                        class="transition-colors cursor-grab active:cursor-grabbing {{ !$badge->is_active ? 'opacity-50' : '' }}">
                                        <td class="px-3 py-4 whitespace-nowrap text-center">
                                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                            </svg>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $badge->sort_order }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                @if ($badge->icon)
                                                    <img src="{{ Storage::url($badge->icon) }}" class="w-8 h-8 rounded mr-3" alt="{{ $badge->name }}">
                                                @else
                                                    <div class="w-8 h-8 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center mr-3">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $badge->display_name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $badge->slug }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge->category->badgeClasses() }}">
                                                {{ $badge->category->label() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            @if ($badge->stars > 0)
                                                <span class="text-yellow-500">{{ str_repeat('★', $badge->stars) }}</span><span class="text-gray-300 dark:text-gray-600">{{ str_repeat('★', 3 - $badge->stars) }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @if ($badge->threshold_type && $badge->threshold_value)
                                                {{ number_format($badge->threshold_value, 0, ',', '.') }}
                                                <span class="text-xs">({{ $badge->threshold_type }})</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="viewWinners({{ $badge->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                                {{ $badge->users_count }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="toggleActive({{ $badge->id }})" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer {{ $badge->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                                {{ $badge->is_active ? 'Attivo' : 'Disattivato' }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="edit({{ $badge->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                Modifica
                                            </button>
                                            <button wire:click="confirmDelete({{ $badge->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Elimina
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessun badge trovato. Esegui il seeder o crea il primo badge.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $badges->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Crea/Modifica --}}
    <x-dialog-modal wire:model.live="showModal" maxWidth="3xl">
        <x-slot name="title">
            {{ $editingId ? 'Modifica Badge' : 'Nuovo Badge' }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="name" value="Nome" />
                        <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" placeholder="es. Rilevatore" />
                        <x-input-error for="name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="slug" value="Slug" />
                        <x-input wire:model="slug" id="slug" type="text" class="mt-1 block w-full" placeholder="es. rilevatore-1-stella" />
                        <x-input-error for="slug" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-label for="description" value="Descrizione (testo mostrato all'utente)" />
                    <textarea wire:model="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error for="description" class="mt-2" />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-label for="category" value="Categoria" />
                        <select wire:model="category" id="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="category" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="stars" value="Stelle" />
                        <select wire:model="stars" id="stars" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="0">Nessuna</option>
                            <option value="1">1 stella</option>
                            <option value="2">2 stelle</option>
                            <option value="3">3 stelle</option>
                        </select>
                    </div>
                    <div>
                        <x-label for="sort_order" value="Ordine" />
                        <x-input wire:model="sort_order" id="sort_order" type="number" class="mt-1 block w-full" min="0" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="threshold_type" value="Tipo Soglia" />
                        <select wire:model="threshold_type" id="threshold_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">Nessuna</option>
                            <option value="count">Conteggio tracce</option>
                            <option value="daily_streak_week">Streak giornaliero (settimana)</option>
                            <option value="bike_days_week">Giorni bici (settimana)</option>
                            <option value="tpl_count">Conteggio TPL</option>
                            <option value="bike_distance_km">Distanza bici (km)</option>
                            <option value="tpl_distance_km">Distanza TPL (km)</option>
                            <option value="multi_modal_distance_km">Distanza multi-modale (km)</option>
                            <option value="co2_kg">CO2 risparmiata (kg)</option>
                            <option value="calories">Calorie bruciate</option>
                            <option value="money_euro">Risparmio economico (EUR)</option>
                        </select>
                    </div>
                    <div>
                        <x-label for="threshold_value" value="Valore Soglia" />
                        <x-input wire:model="threshold_value" id="threshold_value" type="number" step="0.01" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="icon" value="Icona (immagine)" />
                        <input wire:model="icon" id="icon" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300" />
                        <x-input-error for="icon" class="mt-2" />
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="flex items-center">
                            <input wire:model="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Badge attivo</span>
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

    {{-- Modal Vincitori --}}
    <x-dialog-modal wire:model.live="showWinnersModal" maxWidth="2xl">
        <x-slot name="title">
            Vincitori: {{ $viewingBadge?->display_name ?? '' }}
        </x-slot>

        <x-slot name="content">
            @if ($winners && $winners->count() > 0)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($winners as $winner)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $winner->id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $winner->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $winner->email }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($winner->pivot->earned_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $winners->links() }}
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">Nessun utente ha ancora vinto questo badge.</p>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showWinnersModal', false)">
                Chiudi
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- Modal Conferma Eliminazione --}}
    <x-confirmation-modal wire:model.live="showDeleteModal">
        <x-slot name="title">
            Elimina Badge
        </x-slot>

        <x-slot name="content">
            Sei sicuro di voler eliminare questo badge? Tutti i premi assegnati agli utenti verranno rimossi. Questa azione non puo essere annullata.
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
