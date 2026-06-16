<?php

namespace App\Livewire\Partner;

use App\Enums\CompetitionStatus;
use App\Enums\RewardMode;
use App\Models\Competition;
use Livewire\Component;
use Livewire\WithPagination;

class Competitions extends Component
{
    use WithPagination;

    // Tab attivo
    public string $activeTab = 'available';

    // Filtri
    public string $search = '';
    public string $filterStatus = '';

    // Modal dettaglio
    public bool $showDetailModal = false;
    public ?Competition $selectedCompetition = null;

    // Modal conferma iscrizione/disiscrizione
    public bool $showSubscribeModal = false;
    public bool $showUnsubscribeModal = false;
    public ?int $competitionIdToProcess = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
        $this->reset(['search', 'filterStatus']);
    }

    /**
     * Mostra dettaglio gara
     */
    public function showDetail(int $id): void
    {
        $this->selectedCompetition = Competition::with(['ente', 'organizer'])
            ->withCount(['approvedUsers', 'approvedPartners'])
            ->find($id);

        if ($this->selectedCompetition) {
            $this->showDetailModal = true;
        }
    }

    /**
     * Apri modal conferma iscrizione
     */
    public function confirmSubscribe(int $id): void
    {
        $this->competitionIdToProcess = $id;
        $this->showSubscribeModal = true;
    }

    /**
     * Apri modal conferma disiscrizione
     */
    public function confirmUnsubscribe(int $id): void
    {
        $this->competitionIdToProcess = $id;
        $this->showUnsubscribeModal = true;
    }

    /**
     * Iscrivi partner alla gara
     */
    public function subscribe(): void
    {
        if (!$this->competitionIdToProcess) {
            return;
        }

        $competition = Competition::find($this->competitionIdToProcess);
        $partner = auth()->user();

        if (!$competition || !$competition->canPartnerSubscribe()) {
            session()->flash('error', 'Impossibile iscriversi a questa gara.');
            $this->showSubscribeModal = false;
            return;
        }

        if ($competition->hasPartner($partner)) {
            session()->flash('error', 'Sei gia iscritto a questa gara.');
            $this->showSubscribeModal = false;
            return;
        }

        // Iscrivi il partner
        $competition->partners()->attach($partner->id, [
            'status' => 'approved', // I partner sono approvati automaticamente
            'registered_at' => now(),
            'approved_at' => now(),
        ]);

        session()->flash('message', 'Iscrizione completata con successo!');
        $this->showSubscribeModal = false;
        $this->competitionIdToProcess = null;
    }

    /**
     * Disiscrivi partner dalla gara
     */
    public function unsubscribe(): void
    {
        if (!$this->competitionIdToProcess) {
            return;
        }

        $competition = Competition::find($this->competitionIdToProcess);
        $partner = auth()->user();

        if (!$competition) {
            session()->flash('error', 'Gara non trovata.');
            $this->showUnsubscribeModal = false;
            return;
        }

        if (!$competition->hasPartner($partner)) {
            session()->flash('error', 'Non sei iscritto a questa gara.');
            $this->showUnsubscribeModal = false;
            return;
        }

        // Rimuovi l'iscrizione
        $competition->partners()->detach($partner->id);

        session()->flash('message', 'Disiscrizione completata.');
        $this->showUnsubscribeModal = false;
        $this->competitionIdToProcess = null;
    }

    /**
     * Gare disponibili per iscrizione
     */
    protected function getAvailableCompetitions()
    {
        $partnerId = auth()->id();

        $query = Competition::query()
            ->availableForPartners()
            ->withCount(['approvedUsers', 'approvedPartners'])
            ->whereDoesntHave('partners', function ($q) use ($partnerId) {
                $q->where('user_id', $partnerId);
            })
            ->with(['ente']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy('start_date', 'asc')->paginate(10);
    }

    /**
     * Gare a cui il partner e iscritto
     */
    protected function getMyCompetitions()
    {
        $partnerId = auth()->id();

        $query = Competition::query()
            ->whereHas('partners', function ($q) use ($partnerId) {
                $q->where('user_id', $partnerId);
            })
            ->withCount(['approvedUsers', 'approvedPartners'])
            ->with(['ente']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy('end_date', 'desc')->paginate(10);
    }

    public function render()
    {
        $competitions = $this->activeTab === 'available'
            ? $this->getAvailableCompetitions()
            : $this->getMyCompetitions();

        // Statistiche partner
        $partnerId = auth()->id();
        $stats = [
            'subscribed' => Competition::whereHas('partners', fn($q) => $q->where('user_id', $partnerId))->count(),
            'active' => Competition::whereHas('partners', fn($q) => $q->where('user_id', $partnerId))
                ->active()
                ->count(),
        ];

        return view('livewire.partner.competitions', [
            'competitions' => $competitions,
            'stats' => $stats,
            'statuses' => [
                CompetitionStatus::PUBLISHED->value => CompetitionStatus::PUBLISHED->label(),
                CompetitionStatus::ACTIVE->value => CompetitionStatus::ACTIVE->label(),
                CompetitionStatus::ENDED->value => CompetitionStatus::ENDED->label(),
            ],
        ]);
    }
}
