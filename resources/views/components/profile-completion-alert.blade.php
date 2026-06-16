@php
    $user = auth()->user();
    $missing = $user?->missingProfileFields() ?? [];
@endphp

@if(!empty($missing))
    <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-400 dark:border-amber-600 p-4 rounded-md shadow-sm">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-amber-500 dark:text-amber-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">
                    Completa il tuo profilo per usare tutte le funzionalità
                </h3>
                <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                    La dashboard e le altre sezioni potrebbero apparire vuote o non disponibili finché non compili i seguenti dati obbligatori:
                </p>
                <ul class="mt-2 text-sm text-amber-700 dark:text-amber-300 list-disc list-inside space-y-0.5">
                    @foreach($missing as $field)
                        <li>{{ $field }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('profile.show') }}" class="mt-3 inline-flex items-center px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold uppercase tracking-widest rounded-md transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Completa Profilo
                </a>
            </div>
        </div>
    </div>
@endif
