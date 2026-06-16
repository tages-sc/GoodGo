<x-authentication-card>
    <x-slot name="logo">
        <x-authentication-card-logo />
    </x-slot>

    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center">Registrazione Partner</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Diventa un partner commerciale GoodGo</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-1">Fase 1 di 2 - Al primo accesso potrai completare tutti i dati aziendali</p>
    </div>

    <x-validation-errors class="mb-4" />

    <form wire:submit="register">
        <div class="space-y-4">
            <div>
                <x-label for="name" value="Nome e Cognome *" />
                <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required autofocus />
                <x-input-error for="name" class="mt-2" />
            </div>

            <div>
                <x-label for="email" value="Email *" />
                <x-input wire:model="email" id="email" type="email" class="mt-1 block w-full" required />
                <x-input-error for="email" class="mt-2" />
            </div>

            <div>
                <x-label for="password" value="Password *" />
                <x-input wire:model="password" id="password" type="password" class="mt-1 block w-full" required />
                <x-input-error for="password" class="mt-2" />
            </div>

            <div>
                <x-label for="password_confirmation" value="Conferma Password *" />
                <x-input wire:model="password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full" required />
            </div>

            <div class="border-t dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Dati Aziendali Minimi</h4>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-label for="company_name" value="Ragione Sociale *" />
                        <x-input wire:model="company_name" id="company_name" type="text" class="mt-1 block w-full" required />
                        <x-input-error for="company_name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="vat_number" value="Partita IVA *" />
                        <x-input wire:model="vat_number" id="vat_number" type="text" class="mt-1 block w-full" required />
                        <x-input-error for="vat_number" class="mt-2" />
                    </div>
                </div>
            </div>

            <div>
                <label class="flex items-start">
                    <x-checkbox wire:model="terms" name="terms" class="mt-1" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                        Accetto i <a target="_blank" href="{{ route('terms.show') }}" class="underline hover:text-gray-900 dark:hover:text-gray-100">Termini di Servizio</a> e la <a target="_blank" href="{{ route('policy.show') }}" class="underline hover:text-gray-900 dark:hover:text-gray-100">Privacy Policy</a>
                    </span>
                </label>
                <x-input-error for="terms" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
                Hai gia un account?
            </a>

            <x-button>
                Registrati
            </x-button>
        </div>
    </form>

    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <p class="text-center text-sm text-gray-600 dark:text-gray-400">
            Sei un utente?
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                Registrati come Utente
            </a>
        </p>
    </div>
</x-authentication-card>
