<x-action-section>
    <x-slot name="title">
        Profilo Aziendale
    </x-slot>

    <x-slot name="description">
        Gestisci i dati della tua attivita commerciale.
    </x-slot>

    <x-slot name="content">
        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        <div class="space-y-6">
            {{-- Dati Aziendali --}}
            <div class="border-b dark:border-gray-700 pb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Dati Aziendali</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="company_name" value="Ragione Sociale *" />
                        <x-input wire:model="company_name" id="company_name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="company_name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="company_type" value="Tipo Attivita" />
                        <x-input wire:model="company_type" id="company_type" type="text" class="mt-1 block w-full" placeholder="es. Ristorante, Negozio, ecc." />
                        <x-input-error for="company_type" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="vat_number" value="Partita IVA" />
                        <x-input wire:model="vat_number" id="vat_number" type="text" class="mt-1 block w-full" />
                        <x-input-error for="vat_number" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="fiscal_code" value="Codice Fiscale" />
                        <x-input wire:model="fiscal_code" id="fiscal_code" type="text" class="mt-1 block w-full" />
                        <x-input-error for="fiscal_code" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-label for="company_address" value="Indirizzo Azienda" />
                        <x-input wire:model="company_address" id="company_address" type="text" class="mt-1 block w-full" />
                        <x-input-error for="company_address" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="company_city" value="Comune Azienda" />
                        <x-input wire:model="company_city" id="company_city" type="text" class="mt-1 block w-full" />
                        <x-input-error for="company_city" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="company_province" value="Provincia" />
                            <x-input wire:model="company_province" id="company_province" type="text" class="mt-1 block w-full" maxlength="5" placeholder="es. PI" />
                            <x-input-error for="company_province" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="company_postal_code" value="CAP" />
                            <x-input wire:model="company_postal_code" id="company_postal_code" type="text" class="mt-1 block w-full" maxlength="10" />
                            <x-input-error for="company_postal_code" class="mt-2" />
                        </div>
                    </div>
                    <div class="md:col-span-2 mt-2 pt-4 border-t dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Coordinate Geografiche</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Calcolate automaticamente dall'indirizzo. Puoi modificarle manualmente per maggiore precisione (es. ingresso esatto del locale).
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-label for="latitude" value="Latitudine" />
                                <x-input wire:model="latitude" id="latitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono" placeholder="es. 43.7228" />
                                <x-input-error for="latitude" class="mt-2" />
                            </div>
                            <div>
                                <x-label for="longitude" value="Longitudine" />
                                <x-input wire:model="longitude" id="longitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono" placeholder="es. 10.4017" />
                                <x-input-error for="longitude" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="legal_province" value="Provincia" />
                            <x-input wire:model="legal_province" id="legal_province" type="text" class="mt-1 block w-full" maxlength="5" placeholder="es. PI" />
                            <x-input-error for="legal_province" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="legal_postal_code" value="CAP" />
                            <x-input wire:model="legal_postal_code" id="legal_postal_code" type="text" class="mt-1 block w-full" maxlength="10" />
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="operational_province" value="Provincia" />
                            <x-input wire:model="operational_province" id="operational_province" type="text" class="mt-1 block w-full" maxlength="5" placeholder="es. PI" />
                            <x-input-error for="operational_province" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="operational_postal_code" value="CAP" />
                            <x-input wire:model="operational_postal_code" id="operational_postal_code" type="text" class="mt-1 block w-full" maxlength="10" />
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
                        <x-input wire:model="company_phone" id="company_phone" type="tel" class="mt-1 block w-full" />
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
                        <x-label for="website" value="Sito Web" />
                        <x-input wire:model="website" id="website" type="url" class="mt-1 block w-full" placeholder="https://..." />
                        <x-input-error for="website" class="mt-2" />
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
                        <x-input wire:model="legal_rep_fiscal_code" id="legal_rep_fiscal_code" type="text" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_fiscal_code" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="legal_rep_phone" value="Telefono" />
                        <x-input wire:model="legal_rep_phone" id="legal_rep_phone" type="tel" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_phone" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="legal_rep_email" value="Email" />
                        <x-input wire:model="legal_rep_email" id="legal_rep_email" type="email" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_email" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="legal_rep_birth_place" value="Luogo di Nascita" />
                        <x-input wire:model="legal_rep_birth_place" id="legal_rep_birth_place" type="text" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_birth_place" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="legal_rep_birth_date" value="Data di Nascita" />
                        <x-input wire:model="legal_rep_birth_date" id="legal_rep_birth_date" type="date" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_birth_date" class="mt-2" />
                    </div>
                    <div class="md:col-span-2">
                        <x-label for="legal_rep_address" value="Indirizzo Referente" />
                        <x-input wire:model="legal_rep_address" id="legal_rep_address" type="text" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_address" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="legal_rep_city" value="Comune Referente" />
                        <x-input wire:model="legal_rep_city" id="legal_rep_city" type="text" class="mt-1 block w-full" />
                        <x-input-error for="legal_rep_city" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-label for="legal_rep_province" value="Provincia" />
                            <x-input wire:model="legal_rep_province" id="legal_rep_province" type="text" class="mt-1 block w-full" maxlength="5" placeholder="es. PI" />
                            <x-input-error for="legal_rep_province" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="legal_rep_postal_code" value="CAP" />
                            <x-input wire:model="legal_rep_postal_code" id="legal_rep_postal_code" type="text" class="mt-1 block w-full" maxlength="10" />
                            <x-input-error for="legal_rep_postal_code" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dati Bancari --}}
            <div class="border-b dark:border-gray-700 pb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Dati Bancari</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-label for="iban" value="IBAN" />
                        <x-input wire:model="iban" id="iban" type="text" class="mt-1 block w-full" maxlength="34" />
                        <x-input-error for="iban" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="swift_bic" value="SWIFT/BIC" />
                        <x-input wire:model="swift_bic" id="swift_bic" type="text" class="mt-1 block w-full" maxlength="11" />
                        <x-input-error for="swift_bic" class="mt-2" />
                    </div>
                    <div class="md:col-span-3">
                        <x-label for="bank_name" value="Nome Banca" />
                        <x-input wire:model="bank_name" id="bank_name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="bank_name" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Operatore 1 --}}
            <div class="border-b dark:border-gray-700 pb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Operatore 1</h4>
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
                        <x-label for="operator_fiscal_code" value="Codice Fiscale" />
                        <x-input wire:model="operator_fiscal_code" id="operator_fiscal_code" type="text" class="mt-1 block w-full" />
                        <x-input-error for="operator_fiscal_code" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="operator_phone" value="Telefono" />
                        <x-input wire:model="operator_phone" id="operator_phone" type="tel" class="mt-1 block w-full" />
                        <x-input-error for="operator_phone" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="operator_email" value="Email" />
                        <x-input wire:model="operator_email" id="operator_email" type="email" class="mt-1 block w-full" />
                        <x-input-error for="operator_email" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Operatore 2 --}}
            <div class="border-b dark:border-gray-700 pb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Operatore 2</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="operator2_name" value="Nome" />
                        <x-input wire:model="operator2_name" id="operator2_name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="operator2_name" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="operator2_surname" value="Cognome" />
                        <x-input wire:model="operator2_surname" id="operator2_surname" type="text" class="mt-1 block w-full" />
                        <x-input-error for="operator2_surname" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="operator2_fiscal_code" value="Codice Fiscale" />
                        <x-input wire:model="operator2_fiscal_code" id="operator2_fiscal_code" type="text" class="mt-1 block w-full" />
                        <x-input-error for="operator2_fiscal_code" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Logo e Descrizione --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Logo e Descrizione</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-label for="logo" value="Logo" />
                        <input wire:model="logo" id="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900 dark:file:text-indigo-300 hover:file:bg-indigo-100" />
                        <x-input-error for="logo" class="mt-2" />
                        @if($currentLogo)
                            <div class="mt-2">
                                <img src="{{ Storage::url($currentLogo) }}" alt="Logo attuale" class="h-16 w-16 object-contain rounded" />
                            </div>
                        @endif
                    </div>
                    <div>
                        <x-label for="description" value="Descrizione Attivita" />
                        <textarea wire:model="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Descrivi brevemente la tua attivita..."></textarea>
                        <x-input-error for="description" class="mt-2" />
                    </div>
                </div>
            </div>

            {{-- Pulsante Salva --}}
            <div class="flex justify-end pt-4">
                <x-button wire:click="save">
                    Salva Profilo Aziendale
                </x-button>
            </div>
        </div>
    </x-slot>
</x-action-section>
