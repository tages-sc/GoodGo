<div>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-300" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Modifica Utente: {{ $user->name }}
                            </h3>
                            <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->type->badgeClasses() }}">
                                {{ $user->type->label() }}
                            </span>
                        </div>
                        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Annulla
                        </a>
                    </div>

                    <form wire:submit="save" class="space-y-6">
                        {{-- SEZIONE: Dati Account Base (tutti i tipi) --}}
                        <div class="border-b dark:border-gray-700 pb-6">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Dati Account
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-label for="name" value="Nome *" />
                                    <x-input wire:model="name" id="name" type="text" class="mt-1 block w-full" />
                                    <x-input-error for="name" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="email" value="Email *" />
                                    <x-input wire:model="email" id="email" type="email" class="mt-1 block w-full" />
                                    <x-input-error for="email" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="type" value="Tipo Utente *" />
                                    <select wire:model.live="type" id="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        @foreach($userTypes as $userType)
                                            <option value="{{ $userType->value }}">{{ $userType->label() }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="type" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="platform" value="Piattaforma" />
                                    <select wire:model="platform" id="platform" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Non specificata</option>
                                        @foreach($platforms as $p)
                                            <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="platform" class="mt-2" />
                                </div>
                                <div class="flex items-center pt-6">
                                    <label class="flex items-center">
                                        <x-checkbox wire:model="email_verified" />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Email verificata</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- SEZIONE: Password --}}
                        <div class="border-b dark:border-gray-700 pb-6">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Modifica Password
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Lascia vuoto per mantenere la password attuale.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-label for="password" value="Nuova Password" />
                                    <x-input wire:model="password" id="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                    <x-input-error for="password" class="mt-2" />
                                </div>
                                <div>
                                    <x-label for="password_confirmation" value="Conferma Password" />
                                    <x-input wire:model="password_confirmation" id="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>

                        {{-- SEZIONE: Profilo Utente Generico (USER) --}}
                        @if($type === 'user')
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Profilo Utente
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="username" value="Username" />
                                        <x-input wire:model="username" id="username" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="username" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="birth_date" value="Data di Nascita" />
                                        <x-input wire:model="birth_date" id="birth_date" type="date" class="mt-1 block w-full" />
                                        <x-input-error for="birth_date" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="phone" value="Telefono" />
                                        <x-input wire:model="phone" id="phone" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="phone" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="address" value="Indirizzo" />
                                        <x-input wire:model="address" id="address" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="address" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="city" value="Citta" />
                                        <x-input wire:model="city" id="city" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="city" class="mt-2" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <x-label for="province" value="Provincia" />
                                            <x-input wire:model="province" id="province" type="text" maxlength="2" class="mt-1 block w-full uppercase" placeholder="es. PI" />
                                            <x-input-error for="province" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-label for="postal_code" value="CAP" />
                                            <x-input wire:model="postal_code" id="postal_code" type="text" maxlength="5" class="mt-1 block w-full" />
                                            <x-input-error for="postal_code" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SEZIONE: Profilo Partner (PARTNER) --}}
                        @if($type === 'partner')
                            {{-- Dati Aziendali --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Dati Aziendali
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <x-label for="company_name" value="Ragione Sociale *" />
                                        <x-input wire:model="company_name" id="company_name" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="company_name" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="vat_number" value="Partita IVA *" />
                                        <x-input wire:model="vat_number" id="vat_number" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="vat_number" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="fiscal_code" value="Codice Fiscale" />
                                        <x-input wire:model="fiscal_code" id="fiscal_code" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="fiscal_code" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="company_type" value="Tipo Societa" />
                                        <x-input wire:model="company_type" id="company_type" type="text" class="mt-1 block w-full" placeholder="es. S.r.l., S.p.A." />
                                        <x-input-error for="company_type" class="mt-2" />
                                    </div>
                                    <div class="flex items-center pt-6">
                                        <label class="flex items-center">
                                            <x-checkbox wire:model="is_verified" />
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Partner Verificato</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Codice NFC e Coordinate (solo Super Admin) --}}
                            @if(auth()->user()->isSuperAdmin())
                                <div class="border-b dark:border-gray-700 pb-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Codice NFC
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <x-label for="nfc_code" value="Codice NFC" />
                                            <x-input wire:model="nfc_code" id="nfc_code" type="text" maxlength="50" class="mt-1 block w-full font-mono" />
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Campo riservato, modificabile solo da Super Admin. Massimo 50 caratteri.
                                            </p>
                                            <x-input-error for="nfc_code" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <div class="border-b dark:border-gray-700 pb-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Coordinate Geografiche (override manuale)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-label for="partner_latitude" value="Latitudine" />
                                            <x-input wire:model="partner_latitude" id="partner_latitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono" placeholder="es. 43.7228" />
                                            <x-input-error for="partner_latitude" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-label for="partner_longitude" value="Longitudine" />
                                            <x-input wire:model="partner_longitude" id="partner_longitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono" placeholder="es. 10.4017" />
                                            <x-input-error for="partner_longitude" class="mt-2" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Calcolate automaticamente dal sistema quando il partner aggiorna l'indirizzo aziendale (geocoding via OpenStreetMap).
                                                Modifica manuale riservata al Super Admin per casi di geocoding fallito o impreciso.
                                                @if($user->partnerProfile?->geocoded_at)
                                                    <br><span class="text-gray-400">Ultima geolocalizzazione: {{ $user->partnerProfile->geocoded_at->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Sede Legale --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Sede Legale</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <x-label for="legal_address" value="Indirizzo" />
                                        <x-input wire:model="legal_address" id="legal_address" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="legal_address" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="legal_city" value="Citta" />
                                        <x-input wire:model="legal_city" id="legal_city" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="legal_city" class="mt-2" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <x-label for="legal_province" value="Provincia" />
                                            <x-input wire:model="legal_province" id="legal_province" type="text" maxlength="2" class="mt-1 block w-full uppercase" />
                                            <x-input-error for="legal_province" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-label for="legal_postal_code" value="CAP" />
                                            <x-input wire:model="legal_postal_code" id="legal_postal_code" type="text" maxlength="5" class="mt-1 block w-full" />
                                            <x-input-error for="legal_postal_code" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sede Operativa --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Sede Operativa</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <x-label for="operational_address" value="Indirizzo" />
                                        <x-input wire:model="operational_address" id="operational_address" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="operational_address" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="operational_city" value="Citta" />
                                        <x-input wire:model="operational_city" id="operational_city" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="operational_city" class="mt-2" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <x-label for="operational_province" value="Provincia" />
                                            <x-input wire:model="operational_province" id="operational_province" type="text" maxlength="2" class="mt-1 block w-full uppercase" />
                                            <x-input-error for="operational_province" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-label for="operational_postal_code" value="CAP" />
                                            <x-input wire:model="operational_postal_code" id="operational_postal_code" type="text" maxlength="5" class="mt-1 block w-full" />
                                            <x-input-error for="operational_postal_code" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Contatti Aziendali --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Contatti Aziendali</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="company_phone" value="Telefono" />
                                        <x-input wire:model="company_phone" id="company_phone" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="company_phone" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="company_email" value="Email Aziendale" />
                                        <x-input wire:model="company_email" id="company_email" type="email" class="mt-1 block w-full" />
                                        <x-input-error for="company_email" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="pec" value="PEC" />
                                        <x-input wire:model="pec" id="pec" type="email" class="mt-1 block w-full" />
                                        <x-input-error for="pec" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="partner_website" value="Sito Web" />
                                        <x-input wire:model="partner_website" id="partner_website" type="url" class="mt-1 block w-full" placeholder="https://" />
                                        <x-input-error for="partner_website" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            {{-- Rappresentante Legale --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Rappresentante Legale</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="legal_rep_name" value="Nome" />
                                        <x-input wire:model="legal_rep_name" id="legal_rep_name" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="legal_rep_name" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="legal_rep_surname" value="Cognome" />
                                        <x-input wire:model="legal_rep_surname" id="legal_rep_surname" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="legal_rep_surname" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="legal_rep_fiscal_code" value="Codice Fiscale" />
                                        <x-input wire:model="legal_rep_fiscal_code" id="legal_rep_fiscal_code" type="text" class="mt-1 block w-full uppercase" />
                                        <x-input-error for="legal_rep_fiscal_code" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="legal_rep_phone" value="Telefono" />
                                        <x-input wire:model="legal_rep_phone" id="legal_rep_phone" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="legal_rep_phone" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-label for="legal_rep_email" value="Email" />
                                        <x-input wire:model="legal_rep_email" id="legal_rep_email" type="email" class="mt-1 block w-full" />
                                        <x-input-error for="legal_rep_email" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            {{-- Dati Bancari --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Dati Bancari</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <x-label for="iban" value="IBAN" />
                                        <x-input wire:model="iban" id="iban" type="text" class="mt-1 block w-full uppercase" maxlength="34" />
                                        <x-input-error for="iban" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="bank_name" value="Banca" />
                                        <x-input wire:model="bank_name" id="bank_name" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="bank_name" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="swift_bic" value="SWIFT/BIC" />
                                        <x-input wire:model="swift_bic" id="swift_bic" type="text" class="mt-1 block w-full uppercase" maxlength="11" />
                                        <x-input-error for="swift_bic" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            {{-- Operatore --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Operatore di Riferimento</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="operator_name" value="Nome" />
                                        <x-input wire:model="operator_name" id="operator_name" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="operator_name" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="operator_surname" value="Cognome" />
                                        <x-input wire:model="operator_surname" id="operator_surname" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="operator_surname" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="operator_phone" value="Telefono" />
                                        <x-input wire:model="operator_phone" id="operator_phone" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="operator_phone" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="operator_email" value="Email" />
                                        <x-input wire:model="operator_email" id="operator_email" type="email" class="mt-1 block w-full" />
                                        <x-input-error for="operator_email" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            {{-- Descrizione e Logo --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Descrizione e Logo</h4>
                                <div class="space-y-4">
                                    <div>
                                        <x-label for="partner_description" value="Descrizione Attivita" />
                                        <textarea wire:model="partner_description" id="partner_description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                        <x-input-error for="partner_description" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="partner_logo" value="Logo" />
                                        @if($user->partnerProfile?->logo)
                                            <div class="mt-2 mb-2">
                                                <img src="{{ Storage::url($user->partnerProfile->logo) }}" alt="Logo" class="h-16 w-auto rounded">
                                            </div>
                                        @endif
                                        <input wire:model="partner_logo" id="partner_logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                                        <x-input-error for="partner_logo" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- SEZIONE: Profilo Ente/Organizer --}}
                        @if($type === 'ente' || $type === 'organizer')
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Profilo {{ $type === 'ente' ? 'Ente' : 'Organizzatore' }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($type === 'organizer')
                                        <div class="md:col-span-2">
                                            <x-label for="parent_ente_id" value="Ente di Appartenenza" />
                                            <select wire:model="parent_ente_id" id="parent_ente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                                <option value="">Nessuno</option>
                                                @foreach($enti as $ente)
                                                    <option value="{{ $ente->id }}">{{ $ente->name }}</option>
                                                @endforeach
                                            </select>
                                            <x-input-error for="parent_ente_id" class="mt-2" />
                                        </div>
                                    @endif
                                    <div>
                                        <x-label for="tipologia" value="Tipologia" />
                                        <select wire:model="tipologia" id="tipologia" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">Seleziona...</option>
                                            <option value="comune">Comune</option>
                                            <option value="azienda">Azienda</option>
                                        </select>
                                        <x-input-error for="tipologia" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="location" value="Localita" />
                                        <x-input wire:model="location" id="location" type="text" class="mt-1 block w-full" />
                                        <x-input-error for="location" class="mt-2" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <x-label for="ente_descrizione" value="Descrizione" />
                                        <textarea wire:model="ente_descrizione" id="ente_descrizione" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                                        <x-input-error for="ente_descrizione" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="ente_website" value="Sito Web" />
                                        <x-input wire:model="ente_website" id="ente_website" type="url" class="mt-1 block w-full" placeholder="https://" />
                                        <x-input-error for="ente_website" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="colore" value="Colore Tema" />
                                        <div class="flex items-center mt-1">
                                            <input wire:model="colore" id="colore" type="color" class="h-10 w-14 border-gray-300 dark:border-gray-700 rounded-md shadow-sm" />
                                            <x-input wire:model="colore" type="text" class="ml-2 block w-full" placeholder="#000000" maxlength="7" />
                                        </div>
                                        <x-input-error for="colore" class="mt-2" />
                                    </div>
                                    <div class="flex items-center">
                                        <label class="flex items-center">
                                            <x-checkbox wire:model="iscrizione_moderata" />
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Iscrizioni moderate (richiede approvazione)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Social Links --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Link Social</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="instagram_url" value="Instagram" />
                                        <x-input wire:model="instagram_url" id="instagram_url" type="url" class="mt-1 block w-full" placeholder="https://instagram.com/..." />
                                        <x-input-error for="instagram_url" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="facebook_url" value="Facebook" />
                                        <x-input wire:model="facebook_url" id="facebook_url" type="url" class="mt-1 block w-full" placeholder="https://facebook.com/..." />
                                        <x-input-error for="facebook_url" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="linkedin_url" value="LinkedIn" />
                                        <x-input wire:model="linkedin_url" id="linkedin_url" type="url" class="mt-1 block w-full" placeholder="https://linkedin.com/..." />
                                        <x-input-error for="linkedin_url" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="twitter_url" value="Twitter/X" />
                                        <x-input wire:model="twitter_url" id="twitter_url" type="url" class="mt-1 block w-full" placeholder="https://twitter.com/..." />
                                        <x-input-error for="twitter_url" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            {{-- Logo e Banner --}}
                            <div class="border-b dark:border-gray-700 pb-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Immagini</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="ente_logo" value="Logo" />
                                        @if($user->enteProfile?->logo)
                                            <div class="mt-2 mb-2">
                                                <img src="{{ Storage::url($user->enteProfile->logo) }}" alt="Logo" class="h-16 w-auto rounded">
                                            </div>
                                        @endif
                                        <input wire:model="ente_logo" id="ente_logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                                        <x-input-error for="ente_logo" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-label for="ente_banner" value="Banner" />
                                        @if($user->enteProfile?->banner)
                                            <div class="mt-2 mb-2">
                                                <img src="{{ Storage::url($user->enteProfile->banner) }}" alt="Banner" class="h-16 w-auto rounded">
                                            </div>
                                        @endif
                                        <input wire:model="ente_banner" id="ente_banner" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                                        <x-input-error for="ente_banner" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Info sistema (readonly) --}}
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Informazioni di Sistema</h4>
                            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">ID</dt>
                                    <dd class="font-mono text-gray-900 dark:text-gray-100">{{ $user->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Registrato il</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Ultimo aggiornamento</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500 dark:text-gray-400">Email verificata il</dt>
                                    <dd class="text-gray-900 dark:text-gray-100">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'Mai' }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 transition ease-in-out duration-150">
                                Annulla
                            </a>
                            <x-button type="submit">
                                Salva Modifiche
                            </x-button>
                        </div>
                    </form>

                    {{-- SEZIONE: Gestione Crediti (fuori dal form principale perché passa da CreditService) --}}
                    <div class="border-t dark:border-gray-700 mt-6 pt-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Gestione Crediti
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-label value="Saldo Attuale" />
                                <div class="mt-1 px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 rounded-md font-mono">
                                    {{ number_format((float) $user->credits, 2, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <x-label for="creditsOperation" value="Operazione" />
                                <select wire:model="creditsOperation" id="creditsOperation" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="add">Aggiungi</option>
                                    <option value="subtract">Sottrai</option>
                                </select>
                                <x-input-error for="creditsOperation" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="creditsAmount" value="Importo" />
                                <x-input wire:model="creditsAmount" id="creditsAmount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" />
                                <x-input-error for="creditsAmount" class="mt-2" />
                            </div>
                            <div>
                                <x-button type="button" wire:click="adjustCredits" class="w-full justify-center">
                                    Applica
                                </x-button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-label for="creditsDescription" value="Motivazione" />
                            <x-input wire:model="creditsDescription" id="creditsDescription" type="text" class="mt-1 block w-full" placeholder="Es. regalo di benvenuto, rettifica, ecc." />
                            <x-input-error for="creditsDescription" class="mt-2" />
                        </div>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            Ogni modifica viene registrata in log crediti con saldo prima/dopo e risulta visibile all'utente e in dashboard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
