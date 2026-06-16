<?php

namespace App\Livewire\Admin\Movements;

use App\Enums\MovementStatus;
use App\Enums\MovementType;
use App\Enums\UserType;
use App\Models\Movement;
use App\Models\User;
use App\Services\CreditService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filtri
    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterUser = '';
    public string $filterPartner = '';

    // Modal approvazione/rifiuto
    public bool $showProcessModal = false;
    public ?int $processingId = null;
    public string $processAction = 'approve';
    public string $rejectionReason = '';

    // Modal rettifica manuale
    public bool $showAdjustModal = false;
    public ?int $adjustUserId = null;
    public float $adjustAmount = 0;
    public string $adjustReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
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

        $movement = Movement::findOrFail($this->processingId);

        try {
            if ($this->processAction === 'approve') {
                $creditService->approveMovement($movement, auth()->user());
                session()->flash('message', 'Movimento approvato con successo.');
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

    public function openAdjustModal(?int $userId = null): void
    {
        $this->adjustUserId = $userId;
        $this->adjustAmount = 0;
        $this->adjustReason = '';
        $this->showAdjustModal = true;
    }

    public function createAdjustment(CreditService $creditService): void
    {
        $this->validate([
            'adjustUserId' => 'required|exists:users,id',
            'adjustAmount' => 'required|numeric|not_in:0',
            'adjustReason' => 'required|string|min:5',
        ], [
            'adjustUserId.required' => 'Seleziona un utente.',
            'adjustAmount.required' => 'Inserisci un importo.',
            'adjustAmount.not_in' => 'L\'importo non può essere zero.',
            'adjustReason.required' => 'Inserisci una motivazione.',
            'adjustReason.min' => 'La motivazione deve essere di almeno 5 caratteri.',
        ]);

        try {
            $user = User::findOrFail($this->adjustUserId);
            $creditService->createAdjustment(
                $user,
                $this->adjustAmount,
                $this->adjustReason,
                auth()->user()
            );

            session()->flash('message', 'Rettifica crediti applicata con successo.');
        } catch (\Exception $e) {
            $this->addError('adjust', $e->getMessage());
            return;
        }

        $this->showAdjustModal = false;
    }

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (MovementStatus::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public function getTypes(): array
    {
        $types = [];
        foreach (MovementType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        return $types;
    }

    public function render()
    {
        $query = Movement::query()
            ->with(['user', 'partner', 'competition', 'processor'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterPartner) {
            $query->where('partner_id', $this->filterPartner);
        }

        // Statistiche rapide
        $stats = [
            'pending' => Movement::pending()->count(),
            'approved_today' => Movement::approved()->whereDate('processed_at', today())->count(),
            'total_credits_moved' => Movement::approved()->sum('credits_amount'),
        ];

        return view('livewire.admin.movements.index', [
            'movements' => $query->paginate(20),
            'statuses' => $this->getStatuses(),
            'types' => $this->getTypes(),
            'users' => User::where('type', UserType::USER)->orderBy('name')->get(),
            'partners' => User::where('type', UserType::PARTNER)->orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }
}
