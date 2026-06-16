<div>
    {{-- Benvenuto --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Dashboard {{ $isOrganizer ? 'Organizzatore' : 'Ente' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Benvenuto, {{ auth()->user()->name }}!
        </p>
    </div>

    {{-- Cards Statistiche --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Gare</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $competitionsStats['total'] }}</p>
                    <p class="text-xs text-gray-400">{{ $competitionsStats['active'] }} attive</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Utenti Totali</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($competitionsStats['total_participants'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Partner Attivi</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $competitionsStats['total_partners'] }}</p>
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
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Crediti Utilizzati</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($creditsStats['total_credits'], 0, ',', '.') }}</p>
                    @if($creditsStats['total_euro'] > 0)
                        <p class="text-xs text-gray-400">{{ number_format($creditsStats['total_euro'], 2, ',', '.') }} EUR</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Le Mie Gare --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Le Mie Gare</h4>
            <a href="{{ $isOrganizer ? route('organizer.competitions') : route('ente.competitions') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
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
                                {{ $competition->approved_users_count }} partecipanti - {{ $competition->approved_partners_count }} partner
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
                    Nessuna gara trovata.
                </div>
            @endforelse
        </div>
    </div>
</div>
