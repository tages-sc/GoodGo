<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <div class="mb-4 text-center">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Registrazione Utente</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Crea il tuo account GoodGo</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-label for="name" value="Nome" />
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="given-name" />
                </div>

                <div>
                    <x-label for="surname" value="Cognome" />
                    <x-input id="surname" class="block mt-1 w-full" type="text" name="surname" :value="old('surname')" required autocomplete="family-name" />
                </div>
            </div>

            <div class="mt-4">
                <x-label for="email" value="Email" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="Password" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="Conferma Password" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="invitation_code" value="Codice Invito (opzionale)" />
                <x-input id="invitation_code" class="block mt-1 w-full font-mono" type="text" name="invitation_code" :value="old('invitation_code')" maxlength="6" placeholder="000000" autocomplete="off" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se hai ricevuto un codice invito da un ente, inseriscilo qui.</p>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-start">
                            <x-checkbox name="terms" id="terms" required class="mt-1" />

                            <div class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                                Accetto i <a target="_blank" href="{{ route('terms.show') }}" class="underline hover:text-gray-900 dark:hover:text-gray-100">Termini di Servizio</a> e la <a target="_blank" href="{{ route('policy.show') }}" class="underline hover:text-gray-900 dark:hover:text-gray-100">Privacy Policy</a>
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-6">
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                    Hai gia un account?
                </a>

                <x-button class="ms-4">
                    Registrati
                </x-button>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                Sei un commerciante?
                <a href="{{ route('register.partner') }}" class="font-medium text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300">
                    Registrati come Partner
                </a>
            </p>
        </div>
    </x-authentication-card>
</x-guest-layout>
