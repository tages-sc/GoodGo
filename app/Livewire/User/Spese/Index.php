<?php

namespace App\Livewire\User\Spese;

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
        $userId = auth()->id();

        $query = Movement::forUser($userId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->with(['partner.partnerProfile', 'competition', 'processor'])
            ->orderByDesc('processed_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('partner', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        // Statistiche
        $baseQuery = Movement::forUser($userId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE);

        $stats = [
            'total_count' => (clone $baseQuery)->count(),
            'total_credits' => (float) (clone $baseQuery)->sum('credits_amount'),
            'total_euro' => (float) (clone $baseQuery)->sum('euro_amount'),
        ];

        // Gare per filtro
        $competitions = Movement::forUser($userId)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->whereNotNull('competition_id')
            ->with('competition')
            ->get()
            ->pluck('competition')
            ->unique('id')
            ->filter()
            ->values();

        return view('livewire.user.spese.index', [
            'spese' => $query->paginate(20),
            'stats' => $stats,
            'competitions' => $competitions,
        ]);
    }
}
