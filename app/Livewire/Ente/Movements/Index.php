<?php

namespace App\Livewire\Ente\Movements;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Models\Competition;
use App\Models\Movement;
use App\Services\CreditService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filtri
    public string $search = '';
    public string $filterStatus = '';
    public string $filterCompetition = '';

    // Modal approvazione/rifiuto
    public bool $showProcessModal = false;
    public ?int $processingId = null;
    public string $processAction = 'approve';
    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * IDs delle gare gestite da questo ente (o organizzatore)
     */
    protected function getCompetitionIds(): array
    {
        $user = auth()->user();

        if ($user->isOrganizer()) {
            return Competition::where('organizer_id', $user->id)
                ->pluck('id')
                ->toArray();
        }

        return Competition::where('ente_id', $user->id)
            ->pluck('id')
            ->toArray();
    }

    public function openProcessModal(int $id, string $action = 'approve'): void
    {
        $this->processingId = $id;
        $this->processAction = $action;
        $this->rejectionReason = '';
        $this->showProcessModal = true;
    }

    public function processMovement(CreditService $creditService): void
    {
        if (!$this->processingId) {
            return;
        }

        $competitionIds = $this->getCompetitionIds();

        $movement = Movement::whereIn('competition_id', $competitionIds)
            ->findOrFail($this->processingId);

        try {
            if ($this->processAction === 'approve') {
                $creditService->approveMovement($movement, auth()->user());
                session()->flash('message', 'Movimento approvato con successo. I crediti sono stati scalati all\'utente.');
            } else {
                if (empty($this->rejectionReason)) {
                    $this->addError('rejectionReason', 'La motivazione è obbligatoria.');
                    return;
                }
                $creditService->rejectMovement($movement, auth()->user(), $this->rejectionReason);
                session()->flash('message', 'Movimento rifiutato.');
            }
        } catch (\Exception $e) {
            $this->addError('process', $e->getMessage());
            return;
        }

        $this->showProcessModal = false;
        $this->processingId = null;
    }

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (MovementStatus::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public function render()
    {
        $competitionIds = $this->getCompetitionIds();

        $query = Movement::query()
            ->whereIn('competition_id', $competitionIds)
            ->where('type', MovementType::EXPENSE)
            ->with(['user', 'partner.partnerProfile', 'competition', 'processor'])
            ->orderBy('created_at', 'desc');

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

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        // Statistiche
        $baseQuery = Movement::query()
            ->whereIn('competition_id', $competitionIds)
            ->where('type', MovementType::EXPENSE);

        $stats = [
            'pending' => (clone $baseQuery)->pending()->count(),
            'approved_total' => (clone $baseQuery)->approved()->count(),
            'total_credits' => (float) (clone $baseQuery)->approved()->sum('credits_amount'),
        ];

        // Liste per filtri
        $competitions = Competition::whereIn('id', $competitionIds)
            ->orderBy('name')
            ->get();

        return view('livewire.ente.movements.index', [
            'movements' => $query->paginate(20),
            'statuses' => $this->getStatuses(),
            'competitions' => $competitions,
            'stats' => $stats,
        ]);
    }
}
