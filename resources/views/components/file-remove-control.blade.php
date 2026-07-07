@props([
    'field',            // proprietà upload, es. 'rules_document'
    'removeField',      // flag rimozione, es. 'removeRulesDocument'
    'existing' => null, // path del file attuale (null se assente)
    'removeFlag' => false,
    'editing' => false,
    'label' => 'File attuale',
])

@if($editing && $existing)
    <div class="mt-2 flex items-center gap-3 text-xs" wire:key="remove-{{ $field }}">
        @if($removeFlag)
            <span class="text-red-600 dark:text-red-400">Verrà rimosso al salvataggio.</span>
            <button type="button" wire:click="$set('{{ $removeField }}', false)"
                class="text-indigo-600 dark:text-indigo-400 hover:underline">Annulla</button>
        @else
            <span class="text-gray-500 dark:text-gray-400">{{ $label }}:
                <a href="{{ Storage::url($existing) }}" target="_blank" class="underline">Visualizza</a>
            </span>
            <button type="button" wire:click="$set('{{ $removeField }}', true)"
                class="text-red-600 dark:text-red-400 hover:underline">Rimuovi</button>
        @endif
    </div>
@endif
