<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Message --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            {{-- Info Ente --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background-color: {{ $organization->color }}">
                            {{ strtoupper(substr($organization->name, 0, 2)) }}
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $organization->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $organization->city ?? 'Nessuna citta' }}
                                @if($organization->moderated_subscription)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Iscrizione moderata</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Utenti Iscritti
                        </h3>
                    </div>

                    {{-- Filtri --}}
                    <div class="flex flex-wrap gap-4 mb-6">
                        <div class="flex-1 min-w-[200px]">
                            <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome o email..." class="w-full" />
                        </div>
                        <div>
                            <select wire:model.live="filterStatus" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                                <option value="">Tutti gli stati</option>
                                <option value="pending">In attesa</option>
                                <option value="approved">Approvati</option>
                                <option value="rejected">Rifiutati</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tabella --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Richiesta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Elaborata</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Note</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($members as $member)
                                    <tr class="{{ $member->pivot->status === 'pending' ? 'bg-yellow-50 dark:bg-yellow-900/20' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $member->profile_photo_url }}" alt="{{ $member->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($member->pivot->status)
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">In attesa</span>
                                                    @break
                                                @case('approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Approvato</span>
                                                    @break
                                                @case('rejected')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Rifiutato</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ local_dt($member->pivot->requested_at, 'd/m/Y H:i') ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ local_dt($member->pivot->processed_at, 'd/m/Y H:i') ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                            {{ $member->pivot->notes ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($member->pivot->status === 'pending')
                                                <button wire:click="approve({{ $member->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3">
                                                    Approva
                                                </button>
                                                <button wire:click="confirmReject({{ $member->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Rifiuta
                                                </button>
                                            @elseif($member->pivot->status === 'approved')
                                                <button wire:click="remove({{ $member->id }})" wire:confirm="Sei sicuro di voler rimuovere questo utente dall'ente?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Rimuovi
                                                </button>
                                            @else
                                                <button wire:click="approve({{ $member->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3">
                                                    Riabilita
                                                </button>
                                                <button wire:click="remove({{ $member->id }})" wire:confirm="Sei sicuro di voler rimuovere questo utente dall'ente?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Rimuovi
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nessun utente iscritto a questo ente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginazione --}}
                    <div class="mt-4">
                        {{ $members->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Rifiuto --}}
    <x-dialog-modal wire:model.live="showRejectModal">
        <x-slot name="title">
            Rifiuta Iscrizione
        </x-slot>

        <x-slot name="content">
            <div>
                <x-label for="rejectNotes" value="Motivazione (opzionale)" />
                <textarea wire:model="rejectNotes" id="rejectNotes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Inserisci una motivazione per il rifiuto..."></textarea>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showRejectModal', false)">
                Annulla
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="reject">
                Rifiuta Iscrizione
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
