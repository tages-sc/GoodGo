<?php

namespace App\Livewire\User\Movements;

use App\Enums\MovementStatus;
use App\Models\Competition;
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

    // Modal nuova richiesta
    public bool $showRequestModal = false;
    public ?int $selectedCompetitionId = null;
    public ?int $selectedPartnerId = null;
    public string $requestDescription = '';
    public float $requestCredits = 0;
    public float $requestEuro = 0;

    // Dati dinamici per il form
    public array $availablePartners = [];
    public float $creditsToEuro = 0;
    public ?float $maxEuroLimit = null;
    public float $alreadySpentEuro = 0;

    // Modal annullamento
    public bool $showCancelModal = false;
    public ?int $cancellingId = null;

    protected function rules(): array
    {
        return [
            'selectedCompetitionId' => 'required|exists:competitions,id',
            'selectedPartnerId' => 'required|exists:users,id',
            'requestDescription' => 'required|string|min:3|max:500',
            'requestCredits' => 'required|numeric|min:0.01',
        ];
    }

    protected $messages = [
        'selectedCompetitionId.required' => 'Seleziona una gara.',
        'selectedPartnerId.required' => 'Seleziona un partner.',
        'requestDescription.required' => 'Inserisci una causale.',
        'requestDescription.min' => 'La causale deve avere almeno 3 caratteri.',
        'requestCredits.required' => 'Inserisci l\'importo in crediti.',
        'requestCredits.min' => 'L\'importo deve essere maggiore di 0.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ==================== FORM RICHIESTA ====================

    public function openRequestModal(): void
    {
        $this->reset(['selectedCompetitionId', 'selectedPartnerId', 'requestDescription', 'requestCredits', 'requestEuro', 'availablePartners', 'creditsToEuro', 'maxEuroLimit', 'alreadySpentEuro']);
        $this->showRequestModal = true;
    }

    /**
     * Quando l'utente seleziona una gara, carica i partner disponibili e il tasso di cambio
     */
    public function updatedSelectedCompetitionId($value): void
    {
        $this->selectedPartnerId = null;
        $this->requestCredits = 0;
        $this->requestEuro = 0;
        $this->availablePartners = [];
        $this->creditsToEuro = 0;
        $this->maxEuroLimit = null;
        $this->alreadySpentEuro = 0;

        if (!$value) {
            return;
        }

        $competition = Competition::find($value);
        if (!$competition) {
            return;
        }

        // Carica partner approvati della gara
        $partners = $competition->approvedPartners()->get();
        $this->availablePartners = $partners->map(fn(User $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'company' => $p->partnerProfile?->company_name ?? $p->name,
        ])->toArray();

        // Tasso di cambio crediti -> euro
        $this->creditsToEuro = $competition->credits_to_euro > 0 ? (float) $competition->credits_to_euro : 0;

        // Massimo guadagno/spesa a persona (in EUR)
        $this->maxEuroLimit = $competition->max_earning_per_person ? (float) $competition->max_earning_per_person : null;

        // Quanto ha gia speso l'utente in questa gara (movimenti approvati + pending)
        $this->alreadySpentEuro = (float) Movement::forUser(auth()->id())
            ->forCompetition($competition->id)
            ->whereIn('status', [MovementStatus::PENDING->value, MovementStatus::APPROVED->value])
            ->sum('euro_amount');
    }

    /**
     * Calcolo automatico EUR quando cambiano i crediti
     */
    public function updatedRequestCredits($value): void
    {
        $credits = (float) $value;
        if ($this->creditsToEuro > 0 && $credits > 0) {
            $this->requestEuro = round($credits / $this->creditsToEuro, 2);
        } else {
            $this->requestEuro = 0;
        }
    }

    /**
     * Invia la richiesta di movimento
     */
    public function submitRequest(CreditService $creditService): void
    {
        $this->validate();

        $user = auth()->user();
        $competition = Competition::findOrFail($this->selectedCompetitionId);
        $partner = User::findOrFail($this->selectedPartnerId);

        // Verifica crediti sufficienti
        if ($user->credits < $this->requestCredits) {
            $this->addError('requestCredits', 'Crediti insufficienti. Disponibili: ' . number_format($user->credits, 2, ',', '.'));
            return;
        }

        // Verifica limite EUR se presente
        if ($this->maxEuroLimit !== null && $this->requestEuro > 0) {
            $remainingEuro = $this->maxEuroLimit - $this->alreadySpentEuro;
            if ($this->requestEuro > $remainingEuro) {
                $this->addError('requestCredits', 'Superato il limite di spesa per questa gara. Disponibili: ' . number_format($remainingEuro, 2, ',', '.') . ' EUR');
                return;
            }
        }

        try {
            $creditService->createExpenseRequest(
                user: $user,
                partner: $partner,
                creditsAmount: $this->requestCredits,
                description: $this->requestDescription,
                competitionId: $competition->id,
                euroAmount: $this->requestEuro > 0 ? $this->requestEuro : null,
            );

            $this->showRequestModal = false;
            session()->flash('message', 'Richiesta di spesa inviata con successo. Il partner dovra approvarla.');
        } catch (\Exception $e) {
            $this->addError('requestCredits', $e->getMessage());
        }
    }

    // ==================== ANNULLAMENTO ====================

    public function openCancelModal(int $id): void
    {
        $this->cancellingId = $id;
        $this->showCancelModal = true;
    }

    public function cancelMovement(CreditService $creditService): void
    {
        if (!$this->cancellingId) {
            return;
        }

        $movement = Movement::forUser(auth()->id())->findOrFail($this->cancellingId);

        try {
            $creditService->cancelMovement($movement);
            session()->flash('message', 'Richiesta annullata.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->showCancelModal = false;
        $this->cancellingId = null;
    }

    // ==================== HELPERS ====================

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (MovementStatus::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    /**
     * Gare a cui l'utente e iscritto con reward_mode = credits_based
     */
    public function getUserCompetitions(): array
    {
        $competitions = auth()->user()->competitions()
            ->where('reward_mode', 'credits_based')
            ->whereIn('competitions.status', ['active', 'ended'])
            ->orderByDesc('competitions.end_date')
            ->get();

        return $competitions->map(fn(Competition $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'status' => $c->status->value,
            'credits_to_euro' => $c->credits_to_euro,
        ])->toArray();
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Movement::forUser($userId)
            ->with(['partner', 'competition', 'processor'])
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('partner', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Statistiche
        $stats = [
            'balance' => auth()->user()->credits,
            'pending' => Movement::forUser($userId)->pending()->count(),
            'total_spent' => (float) Movement::forUser($userId)->approved()->expenses()->sum('credits_amount'),
            'total_euro' => (float) Movement::forUser($userId)->approved()->expenses()->sum('euro_amount'),
        ];

        return view('livewire.user.movements.index', [
            'movements' => $query->paginate(20),
            'statuses' => $this->getStatuses(),
            'stats' => $stats,
            'userCompetitions' => $this->getUserCompetitions(),
        ]);
    }
}
