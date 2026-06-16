<div>
    {{-- Benvenuto --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Benvenuto, {{ auth()->user()->name }}!
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Gestisci le tue gare e monitora le richieste di spesa.
        </p>
    </div>

    {{-- Statistiche Gare --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gare Totali</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $competitionsStats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gare Attive</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $competitionsStats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Richieste in Attesa</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $movementsStats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Crediti Gestiti</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($movementsStats['total_credits'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Le Mie Gare --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Le Mie Gare</h4>
                <a href="{{ route('partner.competitions') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Vedi tutte
                </a>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($competitions as $competition)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $competition->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $competition->ente->name ?? 'N/D' }} - {{ $competition->approved_users_count }} partecipanti
                                </p>
                            </div>
                            <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full {{ $competition->status->badgeClasses() }}">
                                {{ $competition->status->label() }}
                            </span>
                        </div>
                        <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            {{ $competition->start_date->format('d/m/Y') }} - {{ $competition->end_date->format('d/m/Y') }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <p>Non sei iscritto a nessuna gara.</p>
                        <a href="{{ route('partner.competitions') }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            Iscriviti ora
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Statistiche Movimenti --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Riepilogo Movimenti</h4>
                <a href="{{ route('partner.movements') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Vedi tutti
                </a>
            </div>
            <div class="p-4">
                {{-- Statistiche movimenti --}}
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $movementsStats['pending'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">In Attesa</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $movementsStats['approved'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Approvati</p>
                    </div>
                    <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $movementsStats['rejected'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rifiutati</p>
                    </div>
                </div>

                @if($movementsStats['total_euro'] > 0)
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Valore Totale Gestito</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($movementsStats['total_euro'], 2, ',', '.') }} EUR</p>
                    </div>
                @endif

                {{-- Ultimi movimenti --}}
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Ultimi Movimenti</h5>
                <div class="space-y-2">
                    @forelse($recentMovements as $movement)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ $movement->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium {{ $movement->status === \App\Enums\MovementStatus::PENDING ? 'text-yellow-600' : ($movement->status === \App\Enums\MovementStatus::APPROVED ? 'text-green-600' : 'text-red-600') }}">
                                    {{ number_format($movement->credits_amount, 0) }} crediti
                                </p>
                                <span class="px-1 py-0.5 text-xs rounded {{ $movement->status->badgeClasses() }}">
                                    {{ $movement->status->label() }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">
                            Nessun movimento ricevuto.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
