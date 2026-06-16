<div wire:poll.60s>
    {{-- Benvenuto --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Dashboard Amministratore
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Panoramica generale del sistema GoodGo.
        </p>
    </div>

    {{-- Cards Statistiche --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Gare Attive</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->stats['active_competitions'] }}</p>
                    <p class="text-xs text-gray-400">{{ $this->stats['total_competitions'] }} totali</p>
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
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Utenti Registrati</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($this->stats['total_users'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">{{ $this->stats['active_users'] }} attivi (30gg)</p>
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
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Crediti Circolanti</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($this->stats['total_credits'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtro Temporale --}}
    <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Periodo:</span>
            <select wire:model.live="dateRange" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                <option value="7d">Ultimi 7 giorni</option>
                <option value="30d">Ultimi 30 giorni</option>
                <option value="3m">Ultimi 3 mesi</option>
                <option value="year">Anno corrente</option>
                <option value="custom">Personalizzato</option>
            </select>
            @if($dateRange === 'custom')
                <input type="date" wire:model.live="dateFrom" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <span class="text-gray-500">-</span>
                <input type="date" wire:model.live="dateTo" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            @endif
        </div>
    </div>

    {{-- Grafici --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Andamento Iscrizioni --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Andamento Iscrizioni</h4>
            <div style="height: 280px;">
                <canvas
                    id="registrationsChart"
                    wire:ignore
                    x-data
                    x-init="
                        let ctx = $el.getContext('2d');
                        let chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: @js($this->registrationsChartData['labels']),
                                datasets: [{
                                    label: 'Nuovi utenti',
                                    data: @js($this->registrationsChartData['data']),
                                    borderColor: 'rgb(79, 70, 229)',
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 2,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                                    x: { ticks: { maxTicksLimit: 10 } }
                                }
                            }
                        });
                        $wire.on('charts-updated', (data) => {
                            chart.data.labels = data[0].registrations.labels;
                            chart.data.datasets[0].data = data[0].registrations.data;
                            chart.update();
                        });
                    "
                ></canvas>
            </div>
        </div>

        {{-- Crediti Generati --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Crediti Generati per Giorno</h4>
            <div style="height: 280px;">
                <canvas
                    id="creditsChart"
                    wire:ignore
                    x-data
                    x-init="
                        let ctx = $el.getContext('2d');
                        let chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @js($this->creditsChartData['labels']),
                                datasets: [{
                                    label: 'Crediti',
                                    data: @js($this->creditsChartData['data']),
                                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                    borderColor: 'rgb(16, 185, 129)',
                                    borderWidth: 1,
                                    borderRadius: 3,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true },
                                    x: { ticks: { maxTicksLimit: 10 } }
                                }
                            }
                        });
                        $wire.on('charts-updated', (data) => {
                            chart.data.labels = data[0].credits.labels;
                            chart.data.datasets[0].data = data[0].credits.data;
                            chart.update();
                        });
                    "
                ></canvas>
            </div>
        </div>
    </div>

    {{-- Top 10 Gare --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Top 10 Gare per Partecipazione</h4>
        <div style="height: 300px;">
            <canvas
                id="topCompetitionsChart"
                wire:ignore
                x-data
                x-init="
                    let ctx = $el.getContext('2d');
                    let chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @js($this->topCompetitionsData['labels']),
                            datasets: [{
                                label: 'Partecipanti',
                                data: @js($this->topCompetitionsData['data']),
                                backgroundColor: [
                                    'rgba(79, 70, 229, 0.7)',
                                    'rgba(16, 185, 129, 0.7)',
                                    'rgba(245, 158, 11, 0.7)',
                                    'rgba(239, 68, 68, 0.7)',
                                    'rgba(139, 92, 246, 0.7)',
                                    'rgba(14, 165, 233, 0.7)',
                                    'rgba(236, 72, 153, 0.7)',
                                    'rgba(34, 197, 94, 0.7)',
                                    'rgba(251, 146, 60, 0.7)',
                                    'rgba(99, 102, 241, 0.7)',
                                ],
                                borderWidth: 1,
                                borderRadius: 3,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { stepSize: 1 } }
                            }
                        }
                    });
                    $wire.on('charts-updated', (data) => {
                        chart.data.labels = data[0].topCompetitions.labels;
                        chart.data.datasets[0].data = data[0].topCompetitions.data;
                        chart.update();
                    });
                "
            ></canvas>
        </div>
    </div>
</div>
