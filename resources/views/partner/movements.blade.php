<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Richieste di Spesa') }}
        </h2>
    </x-slot>

    <livewire:partner.movements />
</x-app-layout>
