<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.tracks.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        Traccia #{{ $track->id }}
                    </h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $this->getStatusBadgeClass() }}">
                        {{ $track->status->label() }}
                    </span>
                </div>
            </div>

            {{-- Contenuto principale --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Colonna Mappa (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Mappa Percorso</h3>
                            <div id="track-map" style="height: 450px; border-radius: 0.5rem;" class="bg-gray-100 dark:bg-gray-700"></div>

                            {{-- Legenda --}}
                            <div class="mt-3 flex flex-wrap gap-3 text-xs">
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1"></span> Piedi</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span> Bici</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-amber-500 mr-1"></span> Treno</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-violet-500 mr-1"></span> Bus</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span> Auto</span>
                                <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-orange-500 mr-1"></span> Moto</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Colonna Info (1/3) --}}
                <div class="space-y-6">
                    {{-- Info Traccia --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Dettagli</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Gara</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->competition?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Inizio</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ local_dt($track->started_at, 'd/m/Y H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Fine</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ local_dt($track->ended_at, 'd/m/Y H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Durata</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->duration_formatted }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Distanza totale</dt>
                                <dd class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($track->total_distance_km, 2) }} km</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Multimodale</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $track->is_multimodal ? 'Si' : 'No' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Crediti & Emissioni --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Crediti & Emissioni</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Crediti guadagnati</dt>
                                <dd class="text-green-600 dark:text-green-400 font-bold">{{ number_format($track->credits_earned ?? 0, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">CO2 risparmiata</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ number_format(($track->co2_saved_grams ?? 0) / 1000, 3) }} kg</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Calorie bruciate</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ number_format($track->calories_burned ?? 0, 0) }} kcal</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Stato Validazione --}}
                    @if($track->rejection_reason)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Validazione</h3>
                            <div class="text-xs bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-2 text-red-600 dark:text-red-400">
                                {{ $track->rejection_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabella Segmenti --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Segmenti ({{ $track->segments->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Modalita</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Distanza</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Durata</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vel. Media</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($track->segments as $segment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $segment->sequence }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                @switch($segment->transport_mode->value)
                                                    @case('walk') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                                    @case('bike') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                                    @case('train') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 @break
                                                    @case('bus') bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300 @break
                                                    @case('car') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @break
                                                    @case('motorcycle') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch
                                            ">
                                                {{ $segment->transport_mode->label() }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ number_format($segment->distance_km, 2) }} km</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ $segment->duration_formatted }}</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">{{ $segment->avg_speed_kmh ? number_format($segment->avg_speed_kmh, 1) . ' km/h' : '-' }}</td>
                                        <td class="px-3 py-2 text-center text-sm text-gray-900 dark:text-gray-100">
                                            @if($segment->generates_credits)
                                                {{ number_format($segment->credits_earned ?? 0, 2) }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($segment->status === 'valid') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                @elseif($segment->status === 'invalid') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @endif
                                            ">
                                                {{ ucfirst($segment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Leaflet Map Script --}}
    @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endassets

    @script
    <script>
        const mapData = @json($mapData);

        if (mapData.segments.length > 0) {
            const map = L.map('track-map');

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            mapData.segments.forEach(segment => {
                if (segment.polyline && segment.polyline.length > 0) {
                    L.polyline(segment.polyline, {
                        color: segment.color,
                        weight: 4,
                        opacity: 0.8
                    }).addTo(map).bindPopup(
                        `<strong>${segment.transport_mode_label}</strong><br>` +
                        `Distanza: ${segment.distance_km} km<br>` +
                        `Durata: ${segment.duration || '-'}<br>` +
                        `Vel. media: ${segment.avg_speed_kmh ? segment.avg_speed_kmh + ' km/h' : '-'}`
                    );
                }
            });

            if (mapData.start[0] && mapData.start[1]) {
                L.marker(mapData.start).addTo(map).bindPopup('Partenza');
            }
            if (mapData.end[0] && mapData.end[1]) {
                L.marker(mapData.end).addTo(map).bindPopup('Arrivo');
            }

            if (mapData.bounds) {
                map.fitBounds([
                    [mapData.bounds.south, mapData.bounds.west],
                    [mapData.bounds.north, mapData.bounds.east]
                ], { padding: [20, 20] });
            } else {
                map.setView(mapData.center, mapData.zoom);
            }
        } else {
            document.getElementById('track-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">Nessun dato mappa disponibile</div>';
        }
    </script>
    @endscript
</div>
