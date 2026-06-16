<x-action-section>
    <x-slot name="title">
        Esporta i tuoi dati
    </x-slot>

    <x-slot name="description">
        Scarica una copia di tutti i tuoi dati personali (GDPR Art. 20 - Portabilità dei dati).
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
            Puoi scaricare un file CSV contenente tutti i tuoi dati: profilo, gare, tracce, movimenti, crediti e badge.
        </div>

        <div class="mt-5">
            <x-button wire:click="export">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Scarica i miei dati
            </x-button>
        </div>
    </x-slot>
</x-action-section>
