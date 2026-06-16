<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            {{-- Profilo esteso per utenti generici --}}
            @if(Auth::user()->isUser())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-user-profile')
                </div>

                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.user-statistics')
                </div>

                <x-section-border />
            @endif

            {{-- Profilo ente/organizzatore --}}
            @if(Auth::user()->isEnte() || Auth::user()->isOrganizer())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-ente-profile')
                </div>

                <x-section-border />
            @endif

            {{-- Profilo aziendale partner --}}
            @if(Auth::user()->isPartner())
                <div class="mt-10 sm:mt-0">
                    @livewire('partner.profile-edit')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            {{-- Export dati personali (GDPR Art. 20) --}}
            <x-section-border />

            <div class="mt-10 sm:mt-0">
                @livewire('profile.export-personal-data')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @if(Auth::user()->isUser())
                        @livewire('profile.delete-user-form')
                    @else
                        @livewire('profile.request-deletion')
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
