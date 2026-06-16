<?php

namespace App\Livewire\Admin;

use App\Enums\CompetitionStatus;
use App\Enums\CreditLogType;
use App\Enums\UserType;
use App\Models\Competition;
use App\Models\CreditLog;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public string $dateRange = '30d';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function updatedDateRange(): void
    {
        if ($this->dateRange !== 'custom') {
            $this->dateFrom = null;
            $this->dateTo = null;
        }
        $this->dispatchChartUpdate();
    }

    public function updatedDateFrom(): void
    {
        $this->dispatchChartUpdate();
    }

    public function updatedDateTo(): void
    {
        $this->dispatchChartUpdate();
    }

    protected function dispatchChartUpdate(): void
    {
        $this->dispatch('charts-updated', [
            'registrations' => $this->registrationsChartData,
            'credits' => $this->creditsChartData,
            'topCompetitions' => $this->topCompetitionsData,
        ]);
    }

    protected function getDateBounds(): array
    {
        $end = now()->endOfDay();

        $start = match ($this->dateRange) {
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            '3m' => now()->subMonths(3)->startOfDay(),
            'year' => now()->startOfYear(),
            'custom' => $this->dateFrom ? Carbon::parse($this->dateFrom)->startOfDay() : now()->subDays(29)->startOfDay(),
            default => now()->subDays(29)->startOfDay(),
        };

        if ($this->dateRange === 'custom' && $this->dateTo) {
            $end = Carbon::parse($this->dateTo)->endOfDay();
        }

        return [$start, $end];
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'active_competitions' => Competition::where('status', CompetitionStatus::ACTIVE)->count(),
            'total_competitions' => Competition::count(),
            'total_users' => User::where('type', UserType::USER)->count(),
            'active_users' => User::where('type', UserType::USER)
                ->whereHas('validTracks', fn($q) => $q->where('validated_at', '>=', now()->subDays(30)))
                ->count(),
            'total_credits' => (float) User::where('type', UserType::USER)->sum('credits'),
        ];
    }

    #[Computed]
    public function registrationsChartData(): array
    {
        [$start, $end] = $this->getDateBounds();

        $registrations = User::where('type', UserType::USER)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = [];
        $data = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $data[] = $registrations[$key] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    #[Computed]
    public function creditsChartData(): array
    {
        [$start, $end] = $this->getDateBounds();

        $credits = CreditLog::where('type', CreditLogType::TRACK_VALIDATION)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $data = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $data[] = round((float) ($credits[$key] ?? 0), 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    #[Computed]
    public function topCompetitionsData(): array
    {
        $competitions = Competition::whereIn('status', [CompetitionStatus::ACTIVE, CompetitionStatus::ENDED])
            ->orderByDesc('participants_count')
            ->limit(10)
            ->get(['name', 'participants_count']);

        return [
            'labels' => $competitions->pluck('name')->map(fn($n) => mb_substr($n, 0, 20))->toArray(),
            'data' => $competitions->pluck('participants_count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
