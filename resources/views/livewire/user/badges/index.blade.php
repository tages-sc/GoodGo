<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Riepilogo --}}
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">I tuoi Badge</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Hai sbloccato <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $totalEarned }}</span> badge su <span class="font-bold">{{ $totalBadges }}</span> disponibili
                        </p>
                    </div>
                    <div class="flex items-center">
                        {{-- Barra progresso --}}
                        <div class="w-48 bg-gray-200 dark:bg-gray-700 rounded-full h-3 mr-3">
                            <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: {{ $totalBadges > 0 ? ($totalEarned / $totalBadges * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $totalBadges > 0 ? round($totalEarned / $totalBadges * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>

            {{-- Filtro Categoria --}}
            <div class="mb-6">
                <select wire:model.live="filterCategory" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                    <option value="">Tutte le categorie</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Badge Vinti --}}
            @if ($earnedBadges->count() > 0)
                <div class="mb-8">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Badge Sbloccati</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($earnedBadges as $badge)
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-lg p-5 border-l-4 border-indigo-500">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-4">
                                        @if ($badge->icon)
                                            <img src="{{ Storage::url($badge->icon) }}" class="w-12 h-12 rounded-lg" alt="{{ $badge->name }}">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ $badge->display_name }}
                                            </h5>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge->category->badgeClasses() }}">
                                                {{ $badge->category->label() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $badge->description }}</p>
                                        <p class="text-xs text-indigo-500 dark:text-indigo-400 mt-2">
                                            Ottenuto il {{ isset($earnedDates[$badge->id]) ? \Carbon\Carbon::parse($earnedDates[$badge->id])->format('d/m/Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Badge Da Vincere --}}
            @if ($lockedBadges->count() > 0)
                <div>
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Badge da Sbloccare</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($lockedBadges as $badge)
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg p-5 opacity-60 border-l-4 border-gray-300 dark:border-gray-600">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-4">
                                        <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-sm font-bold text-gray-700 dark:text-gray-300">
                                                {{ $badge->display_name }}
                                            </h5>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge->category->badgeClasses() }}">
                                                {{ $badge->category->label() }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $badge->description }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nessun badge --}}
            @if ($earnedBadges->count() === 0 && $lockedBadges->count() === 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Nessun badge disponibile al momento.</p>
                </div>
            @endif
        </div>
    </div>
</div>
