<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dettaglio Traccia
        </h2>
    </x-slot>

    <livewire:ente.tracks.show :competition="$competition" :track="$track" />
</x-app-layout>
