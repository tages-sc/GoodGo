<?php

namespace App\Livewire\Partner;

use App\Enums\MovementStatus;
use App\Models\Movement;
use App\Services\CreditService;
use Livewire\Component;
use Livewire\WithPagination;

class Movements extends Component
{
    use WithPagination;

    // Filtri
    public string $search = '';
    public string $filterStatus = '';

    // Modal approvazione/rifiuto
    public bool $showProcessModal = false;
    public ?int $processingId = null;
    public string $processAction = 'approve';
    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
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

        $movement = Movement::where('partner_id', auth()->id())
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
        $partnerId = auth()->id();

        $query = Movement::query()
            ->where('partner_id', $partnerId)
            ->with(['user', 'competition'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Statistiche partner
        $stats = [
            'pending' => Movement::where('partner_id', $partnerId)->pending()->count(),
            'approved_total' => Movement::where('partner_id', $partnerId)->approved()->count(),
            'total_credits' => Movement::where('partner_id', $partnerId)->approved()->sum('credits_amount'),
        ];

        return view('livewire.partner.movements', [
            'movements' => $query->paginate(20),
            'statuses' => $this->getStatuses(),
            'stats' => $stats,
        ]);
    }
}
