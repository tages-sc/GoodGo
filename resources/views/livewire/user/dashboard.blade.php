<div>
    {{-- Benvenuto --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Ciao, {{ auth()->user()->name }}!
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Ecco il riepilogo della tua attivita' su GoodGo.
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
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $competitionsStats['subscribed'] }}</p>
                    <p class="text-xs text-gray-400">{{ $competitionsStats['active'] }} attive</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Crediti</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($creditsStats['balance'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($creditsStats['total_earned'], 0, ',', '.') }} guadagnati</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Spese</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $movementsStats['approved'] }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($movementsStats['total_spent'], 0, ',', '.') }} crediti spesi</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Badge</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $badgesStats['earned'] }}/{{ $badgesStats['total'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Km per Modalita --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Km per Modalita' di Trasporto</h4>
            </div>
            <div class="p-4">
                @if(count($kmByMode) > 0)
                    <div class="space-y-4">
                        @php
                            $maxKm = collect($kmByMode)->max('km');
                        @endphp
                        @foreach($kmByMode as $item)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $item['mode']->label() }}
                                    </span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($item['km'], 1, ',', '.') }} km
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                    @php
                                        $color = match($item['mode']) {
                                            \App\Enums\TransportMode::BIKE => 'bg-green-500',
                                            \App\Enums\TransportMode::WALK => 'bg-blue-500',
                                            \App\Enums\TransportMode::TRAIN => 'bg-orange-500',
                                            \App\Enums\TransportMode::BUS => 'bg-purple-500',
                                            default => 'bg-gray-500',
                                        };
                                        $percentage = $maxKm > 0 ? ($item['km'] / $maxKm * 100) : 0;
                                    @endphp
                                    <div class="{{ $color }} h-3 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p>Nessuna traccia registrata. Inizia a muoverti!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Badge Recenti --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Badge Vinti</h4>
                <a href="{{ route('user.badges') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Vedi tutti
                </a>
            </div>
            <div class="p-4">
                {{-- Progresso --}}
                <div class="mb-4">
                    <div class="flex justify-between mb-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Progresso</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $badgesStats['earned'] }}/{{ $badgesStats['total'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $badgesStats['total'] > 0 ? ($badgesStats['earned'] / $badgesStats['total'] * 100) : 0 }}%"></div>
                    </div>
                </div>

                @if($earnedBadges->count() > 0)
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($earnedBadges as $badge)
                            <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                @if ($badge->icon)
                                    <img src="{{ Storage::url($badge->icon) }}" class="w-8 h-8 rounded mr-2" alt="{{ $badge->name }}">
                                @else
                                    <div class="w-8 h-8 rounded bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-2 flex-shrink-0">
                                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">{{ $badge->display_name }}</p>
                                    <p class="text-xs text-gray-400">{{ local_dt($badge->pivot->earned_at, 'd/m/Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <p>Nessun badge ancora. Inizia a registrare tracce!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
