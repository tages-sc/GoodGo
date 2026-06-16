<?php

namespace App\Livewire\Ente\Spese;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\Competition;
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

    /**
     * IDs delle gare gestite da questo ente (o dai suoi organizzatori)
     */
    protected function getCompetitionIds(): array
    {
        $user = auth()->user();

        // Gare dove l'ente e direttamente responsabile
        $ids = Competition::where('ente_id', $user->id)
            ->pluck('id')
            ->toArray();

        // Gare dove l'organizzatore e sotto questo ente
        if ($user->isOrganizer()) {
            $ids = Competition::where('organizer_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        return $ids;
    }

    public function render()
    {
        $competitionIds = $this->getCompetitionIds();

        $query = Movement::query()
            ->whereIn('competition_id', $competitionIds)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE)
            ->with(['user', 'partner.partnerProfile', 'competition', 'processor'])
            ->orderByDesc('processed_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
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
            ->whereIn('competition_id', $competitionIds)
            ->where('status', MovementStatus::APPROVED)
            ->where('type', MovementType::EXPENSE);

        $stats = [
            'total_count' => (clone $baseQuery)->count(),
            'total_credits' => (float) (clone $baseQuery)->sum('credits_amount'),
            'total_euro' => (float) (clone $baseQuery)->sum('euro_amount'),
        ];

        // Liste per filtri
        $competitions = Competition::whereIn('id', $competitionIds)
            ->orderBy('name')
            ->get();

        $partners = Movement::query()
            ->whereIn('competition_id', $competitionIds)
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

        return view('livewire.ente.spese.index', [
            'spese' => $query->paginate(20),
            'stats' => $stats,
            'competitions' => $competitions,
            'partners' => $partners,
        ]);
    }
}
