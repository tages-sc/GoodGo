<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">

            {{-- Titolo --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                Esporta dati in formato CSV
            </h3>

            <div class="space-y-6">
                {{-- Tipo di dato --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo di dato
                    </label>
                    <select wire:model.live="exportType"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="users">Utenti</option>
                        <option value="competitions">Gare</option>
                        <option value="movements">Movimenti</option>
                        <option value="spese">Spese</option>
                    </select>
                </div>

                {{-- Periodo --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Da data
                        </label>
                        <input type="date" wire:model.live="dateFrom"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            A data
                        </label>
                        <input type="date" wire:model.live="dateTo"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                {{-- Descrizione campi --}}
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 font-medium">
                        Campi inclusi nell'export:
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($exportType === 'users')
                            ID, Nome, Email, Tipo, Piattaforma, Crediti, Email Verificata, Data Registrazione, Ultimo Accesso
                        @elseif($exportType === 'competitions')
                            ID, Nome, Slug, Tipo, Estensione, Stato, Data Inizio, Data Fine, Iscritti, Crediti Distribuiti
                        @elseif($exportType === 'movements')
                            ID, Utente, Email Utente, Tipo, Stato, Crediti, Euro, Gara, Partner, Descrizione, Data Richiesta, Data Gestione
                        @elseif($exportType === 'spese')
                            ID, Utente, Email Utente, Partner, Crediti, Euro, Tasso Cambio, Gara, Descrizione, Data Richiesta, Data Approvazione, Approvato da
                        @endif
                    </p>
                </div>

                {{-- Conteggio e bottone --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Record trovati: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $this->recordCount }}</span>
                    </div>

                    <button wire:click="export"
                            @if($this->recordCount === 0) disabled @endif
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Scarica CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
