<?php

namespace App\Livewire\Admin\Spese;

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
    public string $filterPartner = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Movement::query()
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->with(['user', 'partner.partnerProfile', 'competition', 'processor'])
            ->orderByDesc('processed_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('partner', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        if ($this->filterPartner) {
            $query->where('partner_id', $this->filterPartner);
        }

        // Statistiche
        $baseQuery = Movement::query()
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE);

        $stats = [
            'total_count' => (clone $baseQuery)->count(),
            'total_credits' => (float) (clone $baseQuery)->sum('credits_amount'),
            'total_euro' => (float) (clone $baseQuery)->sum('euro_amount'),
        ];

        // Liste per filtri
        $competitions = Movement::query()
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->whereNotNull('competition_id')
            ->distinct('competition_id')
            ->with('competition')
            ->get()
            ->pluck('competition')
            ->unique('id')
            ->filter()
            ->values();

        $partners = Movement::query()
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->whereNotNull('partner_id')
            ->distinct('partner_id')
            ->with('partner.partnerProfile')
            ->get()
            ->pluck('partner')
            ->unique('id')
            ->filter()
            ->values();

        return view('livewire.admin.spese.index', [
            'spese' => $query->paginate(20),
            'stats' => $stats,
            'competitions' => $competitions,
            'partners' => $partners,
        ]);
    }
}
