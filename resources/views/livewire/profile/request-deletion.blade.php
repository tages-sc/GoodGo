<x-action-section>
    <x-slot name="title">
        {{ __('Delete Account') }}
    </x-slot>

    <x-slot name="description">
        Richiedi la cancellazione del tuo account.
    </x-slot>

    <x-slot name="content">
        @if($deletionRequestedAt)
            <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Hai richiesto la cancellazione del tuo account il <strong>{{ $deletionRequestedAt->format('d/m/Y H:i') }}</strong>.
                            L'amministratore verificherà i dati associati al tuo ruolo e procederà con la cancellazione.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                Vista la complessità dei dati gestiti dal tuo ruolo, la cancellazione deve essere verificata dall'amministratore.
                Cliccando il pulsante, verrà inviata una richiesta automatica di cancellazione all'amministratore del sistema.
            </div>

            <div class="mt-5">
                <x-danger-button wire:click="confirmRequest">
                    Richiedi cancellazione account
                </x-danger-button>
            </div>

            @if (session('status') === 'deletion-requested')
                <p class="mt-3 text-sm text-green-600 dark:text-green-400">
                    Richiesta di cancellazione inviata con successo.
                </p>
            @endif
        @endif

        {{-- Modal conferma --}}
        <x-dialog-modal wire:model.live="confirmingRequest">
            <x-slot name="title">
                Conferma richiesta cancellazione
            </x-slot>

            <x-slot name="content">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Sei sicuro di voler richiedere la cancellazione del tuo account? Verrà inviata una notifica all'amministratore che procederà con la verifica e la cancellazione dei tuoi dati.
                </p>
                <p class="mt-3 text-sm text-red-600 dark:text-red-400 font-medium">
                    Questa azione è irreversibile. Tutti i tuoi dati personali verranno eliminati.
                </p>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingRequest')">
                    Annulla
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="sendRequest">
                    Conferma richiesta
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
