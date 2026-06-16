<?php

namespace App\Livewire\Partner\Spese;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\Movement;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterCompetition = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $partnerId = auth()->id();

        $query = Movement::query()
            ->where('partner_id', $partnerId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->with(['user', 'competition', 'processor'])
            ->orderByDesc('processed_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        // Statistiche
        $baseQuery = Movement::query()
            ->where('partner_id', $partnerId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE);

        $stats = [
            'total_count' => (clone $baseQuery)->count(),
            'total_credits' => (float) (clone $baseQuery)->sum('credits_amount'),
            'total_euro' => (float) (clone $baseQuery)->sum('euro_amount'),
        ];

        // Gare per filtro
        $competitions = Movement::query()
            ->where('partner_id', $partnerId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->whereNotNull('competition_id')
            ->with('competition')
            ->get()
            ->pluck('competition')
            ->unique('id')
            ->filter()
            ->values();

        return view('livewire.partner.spese.index', [
            'spese' => $query->paginate(20),
            'stats' => $stats,
            'competitions' => $competitions,
        ]);
    }
}
