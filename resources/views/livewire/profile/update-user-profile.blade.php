<x-form-section submit="save">
    <x-slot name="title">
        Dati Profilo
    </x-slot>

    <x-slot name="description">
        Aggiorna i tuoi dati personali aggiuntivi.
    </x-slot>

    <x-slot name="form">
        <!-- Username -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="username" value="Nome utente" />
            <x-input id="username" type="text" class="mt-1 block w-full" wire:model="username" autocomplete="username" />
            <x-input-error for="username" class="mt-2" />
        </div>

        <!-- Data di Nascita -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="birth_date" value="Data di nascita" />
            <x-input id="birth_date" type="date" class="mt-1 block w-full" wire:model="birth_date" />
            <x-input-error for="birth_date" class="mt-2" />
        </div>

        <!-- Telefono -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="phone" value="Telefono" />
            <x-input id="phone" type="tel" class="mt-1 block w-full" wire:model="phone" autocomplete="tel" />
            <x-input-error for="phone" class="mt-2" />
        </div>

        <!-- Indirizzo -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="address" value="Indirizzo" />
            <x-input id="address" type="text" class="mt-1 block w-full" wire:model="address" autocomplete="street-address" />
            <x-input-error for="address" class="mt-2" />
        </div>

        <!-- Citta e Provincia -->
        <div class="col-span-6 sm:col-span-4">
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <x-label for="city" value="Comune" />
                    <x-input id="city" type="text" class="mt-1 block w-full" wire:model="city" autocomplete="address-level2" />
                    <x-input-error for="city" class="mt-2" />
                </div>
                <div>
                    <x-label for="province" value="Provincia" />
                    <x-input id="province" type="text" class="mt-1 block w-full" wire:model="province" maxlength="2" placeholder="PI" autocomplete="address-level1" />
                    <x-input-error for="province" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- CAP -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="postal_code" value="CAP" />
            <x-input id="postal_code" type="text" class="mt-1 block w-full sm:w-1/3" wire:model="postal_code" maxlength="5" autocomplete="postal-code" />
            <x-input-error for="postal_code" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            Salvato.
        </x-action-message>

        <x-button>
            Salva
        </x-button>
    </x-slot>
</x-form-section>
