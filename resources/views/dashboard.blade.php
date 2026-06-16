<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-profile-completion-alert />

            @if(Auth::user()->isSuperAdmin())
                <livewire:admin.dashboard />
            @elseif(Auth::user()->isEnte() || Auth::user()->isOrganizer())
                <livewire:ente.dashboard />
            @elseif(Auth::user()->isUser())
                <livewire:user.dashboard />
            @elseif(Auth::user()->isPartner())
                <livewire:partner.dashboard />
            @endif
        </div>
    </div>
</x-app-layout>
