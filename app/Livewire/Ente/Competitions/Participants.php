<?php

namespace App\Livewire\Ente\Competitions;

use App\Enums\CreditLogType;
use App\Enums\MovementStatus;
use App\Enums\ParticipationStatus;
use App\Enums\UserType;
use App\Models\Competition;
use App\Models\Movement;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Participants extends Component
{
    use WithPagination;

    public Competition $competition;

    // Filtri
    public string $search = '';
    public string $filterStatus = '';
    public string $sortBy = 'rank';
    public string $sortDirection = 'asc';

    // Modals
    public bool $showStatusModal = false;
    public bool $showCreditsModal = false;
    public ?int $selectedUserId = null;
    public string $newStatus = '';
    public string $rejectionReason = '';
    public string $creditsAmount = '';
    public string $creditsDescription = '';
    public string $creditsOperation = 'add';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'sortBy' => ['except' => 'rank'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(Competition $competition): void
    {
        $this->competition = $competition;
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        // L'utente deve essere l'Ente proprietario o l'Organizzatore assegnato
        if ($user->type === UserType::ENTE) {
            if ($this->competition->ente_id !== $user->id) {
                abort(403, 'Non hai accesso a questa gara.');
            }
        } elseif ($user->type === UserType::ORGANIZER) {
            if ($this->competition->organizer_id !== $user->id) {
                abort(403, 'Non hai accesso a questa gara.');
            }
        } else {
            abort(403, 'Accesso non autorizzato.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ==================== GESTIONE STATO ====================

    public function openStatusModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $pivot = $this->competition->users()->where('user_id', $userId)->first()?->pivot;

        if ($pivot) {
            $this->newStatus = $pivot->status;
            $this->rejectionReason = '';
        }

        $this->showStatusModal = true;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'newStatus' => 'required|in:' . implode(',', ParticipationStatus::values()),
            'rejectionReason' => 'required_if:newStatus,rejected|nullable|string|max:500',
        ]);

        $pivotData = [
            'status' => $this->newStatus,
        ];

        if ($this->newStatus === ParticipationStatus::APPROVED->value) {
            $pivotData['approved_at'] = now();
            $pivotData['approved_by'] = Auth::id();
            $pivotData['rejection_reason'] = null;
        } elseif ($this->newStatus === ParticipationStatus::REJECTED->value) {
            $pivotData['rejection_reason'] = $this->rejectionReason;
        } elseif ($this->newStatus === ParticipationStatus::WITHDRAWN->value) {
            $pivotData['withdrawn_at'] = now();
        }

        $this->competition->users()->updateExistingPivot($this->selectedUserId, $pivotData);

        // Aggiorna statistiche gara
        $this->competition->updateStatistics();

        session()->flash('message', 'Stato partecipante aggiornato.');
        $this->closeStatusModal();
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->selectedUserId = null;
        $this->newStatus = '';
        $this->rejectionReason = '';
    }

    // ==================== GESTIONE CREDITI ====================

    public function openCreditsModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->creditsAmount = '';
        $this->creditsDescription = '';
        $this->creditsOperation = 'add';
        $this->showCreditsModal = true;
    }

    public function updateCredits(CreditService $creditService): void
    {
        $this->validate([
            'creditsAmount' => 'required|numeric|min:0.01',
            'creditsDescription' => 'required|string|max:255',
            'creditsOperation' => 'required|in:add,subtract',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $amount = (float) $this->creditsAmount;
        $signedDelta = $this->creditsOperation === 'add' ? $amount : -$amount;
        $actor = Auth::user();
        $metadata = ['competition_id' => $this->competition->id];

        try {
            DB::transaction(function () use ($creditService, $user, $amount, $signedDelta, $actor, $metadata) {
                if ($this->creditsOperation === 'add') {
                    $creditService->addCredits(
                        $user,
                        $amount,
                        CreditLogType::MANUAL_ADD,
                        $this->creditsDescription,
                        $actor,
                        $metadata
                    );
                } else {
                    $creditService->subtractCredits(
                        $user,
                        $amount,
                        CreditLogType::MANUAL_SUBTRACT,
                        $this->creditsDescription,
                        $actor,
                        $metadata
                    );
                }

                // Mantieni allineato il totale crediti del partecipante nella gara
                if ($this->competition->users()->where('user_id', $user->id)->exists()) {
                    $this->competition->users()->updateExistingPivot($user->id, [
                        'total_credits' => DB::raw('total_credits + ' . $signedDelta),
                    ]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            $this->addError('creditsAmount', $e->getMessage());
            return;
        }

        session()->flash('message', 'Crediti aggiornati con successo.');
        $this->closeCreditsModal();
    }

    public function closeCreditsModal(): void
    {
        $this->showCreditsModal = false;
        $this->selectedUserId = null;
        $this->creditsAmount = '';
        $this->creditsDescription = '';
        $this->creditsOperation = 'add';
    }

    // ==================== APPROVAZIONE RAPIDA ====================

    public function approveUser(int $userId): void
    {
        $this->competition->users()->updateExistingPivot($userId, [
            'status' => ParticipationStatus::APPROVED->value,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $this->competition->updateStatistics();
        session()->flash('message', 'Partecipante approvato.');
    }

    public function rejectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->newStatus = ParticipationStatus::REJECTED->value;
        $this->showStatusModal = true;
    }

    // ==================== EXPORT CSV ====================

    public function exportCsv(): StreamedResponse
    {
        $participants = $this->getParticipantsQuery()->get();

        $filename = 'partecipanti_' . $this->competition->slug . '_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($participants) {
            $handle = fopen('php://output', 'w');

            // BOM per UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($handle, [
                'ID',
                'Nome',
                'Email',
                'Stato',
                'Data Iscrizione',
                'Data Approvazione',
                'Crediti Gara',
                'Tracce',
                'Distanza (km)',
                'CO2 Risparmiata (kg)',
                'Posizione',
                'Spese Totali',
            ], ';');

            foreach ($participants as $participant) {
                fputcsv($handle, [
                    $participant->id,
                    $participant->name,
                    $participant->email,
                    ParticipationStatus::from($participant->pivot->status)->label(),
                    $participant->pivot->registered_at?->format('d/m/Y H:i'),
                    $participant->pivot->approved_at?->format('d/m/Y H:i'),
                    number_format($participant->pivot->total_credits, 2, ',', '.'),
                    $participant->pivot->tracks_count,
                    number_format($participant->pivot->total_distance_km, 2, ',', '.'),
                    number_format($participant->pivot->total_co2_saved_kg, 3, ',', '.'),
                    $participant->pivot->rank ?? '-',
                    number_format($participant->total_expenses ?? 0, 2, ',', '.'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ==================== QUERY ====================

    protected function getParticipantsQuery()
    {
        $query = $this->competition->users()
            ->with(['profile'])
            ->withSum(['movements as total_expenses' => function ($q) {
                $q->where('competition_id', $this->competition->id)
                    ->where('status', MovementStatus::APPROVED);
            }], 'credits_amount');

        // Filtro ricerca
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro stato
        if ($this->filterStatus) {
            $query->wherePivot('status', $this->filterStatus);
        }

        // Ordinamento
        $sortableFields = ['name', 'email', 'rank', 'total_credits', 'tracks_count'];
        $pivotFields = ['rank', 'total_credits', 'tracks_count', 'total_distance_km', 'registered_at'];

        if (in_array($this->sortBy, $pivotFields)) {
            $query->orderByPivot($this->sortBy, $this->sortDirection);
        } elseif (in_array($this->sortBy, $sortableFields)) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.ente.competitions.participants', [
            'participants' => $this->getParticipantsQuery()->paginate(20),
            'statuses' => ParticipationStatus::cases(),
            'totalParticipants' => $this->competition->users()->count(),
            'approvedCount' => $this->competition->approvedUsers()->count(),
            'pendingCount' => $this->competition->pendingUsers()->count(),
        ]);
    }
}
