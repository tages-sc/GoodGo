<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Movimenti e Crediti') }}
        </h2>
    </x-slot>

    <livewire:admin.movements.index />
</x-app-layout>
