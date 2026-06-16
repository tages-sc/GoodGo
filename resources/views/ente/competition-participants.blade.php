<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Partecipanti Gara
        </h2>
    </x-slot>

    <livewire:ente.competitions.participants :competition="$competition" />
</x-app-layout>
