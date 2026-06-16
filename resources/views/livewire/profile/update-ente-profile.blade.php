<x-form-section submit="save">
    <x-slot name="title">
        Profilo {{ Auth::user()->isEnte() ? 'Ente' : 'Organizzatore' }}
    </x-slot>

    <x-slot name="description">
        Gestisci le informazioni del tuo profilo {{ Auth::user()->isEnte() ? 'ente' : 'organizzatore' }}.
    </x-slot>

    <x-slot name="form">
        <!-- Tipologia -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tipologia" value="Tipologia" />
            <select id="tipologia" wire:model="tipologia" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                <option value="">-- Seleziona --</option>
                <option value="comune">Comune</option>
                <option value="azienda">Azienda</option>
            </select>
            <x-input-error for="tipologia" class="mt-2" />
        </div>

        <!-- Location -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="location" value="Sede (Citta)" />
            <x-input id="location" type="text" class="mt-1 block w-full" wire:model="location" />
            <x-input-error for="location" class="mt-2" />
        </div>

        <!-- Descrizione -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="descrizione" value="Descrizione" />
            <textarea id="descrizione" wire:model="descrizione" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
            <x-input-error for="descrizione" class="mt-2" />
        </div>

        <!-- Iscrizione moderata -->
        <div class="col-span-6 sm:col-span-4">
            <label for="iscrizione_moderata" class="flex items-center">
                <x-checkbox id="iscrizione_moderata" wire:model="iscrizione_moderata" />
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Iscrizione moderata (richiede approvazione)</span>
            </label>
            <x-input-error for="iscrizione_moderata" class="mt-2" />
        </div>

        <!-- Colore -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="colore" value="Colore" />
            <div class="flex items-center gap-3 mt-1">
                <input id="colore" type="color" wire:model="colore" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-700 cursor-pointer" />
                <x-input type="text" wire:model="colore" class="w-28" maxlength="7" placeholder="#000000" />
            </div>
            <x-input-error for="colore" class="mt-2" />
        </div>

        <!-- Logo -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="logo" value="Logo" />

            @if ($currentLogo)
                <div class="mt-2 mb-2">
                    <img src="{{ Storage::url($currentLogo) }}" alt="Logo" class="h-16 w-16 object-contain rounded">
                </div>
            @endif

            <input type="file" id="logo" wire:model="logo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800" />

            @if ($logo)
                <div class="mt-2">
                    <img src="{{ $logo->temporaryUrl() }}" alt="Anteprima logo" class="h-16 w-16 object-contain rounded">
                </div>
            @endif

            <x-input-error for="logo" class="mt-2" />
        </div>

        <!-- Banner -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="banner" value="Banner" />

            @if ($currentBanner)
                <div class="mt-2 mb-2">
                    <img src="{{ Storage::url($currentBanner) }}" alt="Banner" class="h-24 w-full object-cover rounded">
                </div>
            @endif

            <input type="file" id="banner" wire:model="banner" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800" />

            @if ($banner)
                <div class="mt-2">
                    <img src="{{ $banner->temporaryUrl() }}" alt="Anteprima banner" class="h-24 w-full object-cover rounded">
                </div>
            @endif

            <x-input-error for="banner" class="mt-2" />
        </div>

        <!-- Website -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="website" value="Sito web" />
            <x-input id="website" type="url" class="mt-1 block w-full" wire:model="website" placeholder="https://..." />
            <x-input-error for="website" class="mt-2" />
        </div>

        <!-- Social Links -->
        <div class="col-span-6 sm:col-span-4">
            <h4 class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-3">Social</h4>

            <div class="space-y-3">
                <div>
                    <x-label for="instagram_url" value="Instagram" />
                    <x-input id="instagram_url" type="url" class="mt-1 block w-full" wire:model="instagram_url" placeholder="https://instagram.com/..." />
                    <x-input-error for="instagram_url" class="mt-2" />
                </div>

                <div>
                    <x-label for="facebook_url" value="Facebook" />
                    <x-input id="facebook_url" type="url" class="mt-1 block w-full" wire:model="facebook_url" placeholder="https://facebook.com/..." />
                    <x-input-error for="facebook_url" class="mt-2" />
                </div>

                <div>
                    <x-label for="linkedin_url" value="LinkedIn" />
                    <x-input id="linkedin_url" type="url" class="mt-1 block w-full" wire:model="linkedin_url" placeholder="https://linkedin.com/..." />
                    <x-input-error for="linkedin_url" class="mt-2" />
                </div>

                <div>
                    <x-label for="twitter_url" value="X (Twitter)" />
                    <x-input id="twitter_url" type="url" class="mt-1 block w-full" wire:model="twitter_url" placeholder="https://x.com/..." />
                    <x-input-error for="twitter_url" class="mt-2" />
                </div>
            </div>
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
