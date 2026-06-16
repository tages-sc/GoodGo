<div>
    <x-action-section>
        <x-slot name="title">
            {{ __('Statistiche') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Riepilogo delle tue emissioni risparmiate e percorrenze per modalita di trasporto.') }}
        </x-slot>

        <x-slot name="content">
            {{-- Emissioni Risparmiate --}}
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Emissioni Risparmiate</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-700 dark:text-green-400">
                        {{ number_format($emissions->total_co2_grams / 1000, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">CO2 (kg)</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-700 dark:text-green-400">
                        {{ number_format($emissions->total_so2_mg, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">SO2 (mg)</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-700 dark:text-green-400">
                        {{ number_format($emissions->total_nox_grams, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">NOx (g)</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-700 dark:text-green-400">
                        {{ number_format($emissions->total_co_grams, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">CO (g)</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-700 dark:text-green-400">
                        {{ number_format($emissions->total_pm10_grams, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">PM10 (g)</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-blue-700 dark:text-blue-400">
                        {{ number_format($emissions->total_calories, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Calorie (kcal)</div>
                </div>
            </div>

            {{-- Percorrenza per Modalita --}}
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Percorrenza per Modalita di Trasporto</h4>
            @if(count($modeDistances) > 0)
                <div class="space-y-3">
                    @foreach($modeDistances as $mode)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $mode['label'] }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($mode['km'], 2) }} km</span>
                        </div>
                        @if($totalDistanceKm > 0)
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 rounded-full" style="width: {{ min(100, ($mode['km'] / $totalDistanceKm) * 100) }}%"></div>
                            </div>
                        @endif
                    @endforeach
                    <div class="flex justify-between items-center pt-2 border-t dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Totale</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalDistanceKm, 2) }} km</span>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Nessuna traccia validata ancora.</p>
            @endif
        </x-slot>
    </x-action-section>
</div>
