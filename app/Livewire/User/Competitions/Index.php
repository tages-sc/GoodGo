<?php

namespace App\Livewire\User\Competitions;

use App\Enums\CompetitionStatus;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $tab = 'available';
    public string $search = '';

    protected $queryString = [
        'tab' => ['except' => 'available'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function subscribe(int $competitionId): void
    {
        $competition = Competition::findOrFail($competitionId);
        $user = Auth::user();

        if ($competition->hasUser($user)) {
            session()->flash('error', 'Sei già iscritto a questa gara.');
            return;
        }

        if (!$competition->isRegistrationOpen()) {
            session()->flash('error', 'Le iscrizioni a questa gara sono chiuse.');
            return;
        }

        $ageEligible = $competition->isUserAgeEligible($user);
        if ($ageEligible === false) {
            $birthDate = $user->profile?->birth_date;
            if (!$birthDate) {
                session()->flash('error', 'Per iscriverti a questa gara devi compilare la data di nascita nel tuo profilo.');
            } else {
                session()->flash('error', 'Non puoi iscriverti a questa gara: la tua età non rientra nelle fasce ammesse (' . implode(', ', $competition->age_range) . ').');
            }
            return;
        }

        $status = $competition->moderated_subscription ? 'pending' : 'approved';

        $competition->users()->attach($user->id, [
            'status' => $status,
            'registered_at' => now(),
            'approved_at' => $status === 'approved' ? now() : null,
        ]);

        if ($status === 'approved') {
            $competition->updateStatistics();
        }

        $message = $status === 'pending'
            ? 'Richiesta di iscrizione inviata. In attesa di approvazione.'
            : 'Iscrizione completata con successo!';
        session()->flash('message', $message);
    }

    public function unsubscribe(int $competitionId): void
    {
        $competition = Competition::findOrFail($competitionId);
        $user = Auth::user();

        if (!$competition->hasUser($user)) {
            session()->flash('error', 'Non sei iscritto a questa gara.');
            return;
        }

        $competition->users()->detach($user->id);
        $competition->updateStatistics();

        session()->flash('message', 'Disiscrizione completata.');
    }

    public function render()
    {
        $user = Auth::user();

        if ($this->tab === 'mine') {
            $query = $user->competitions()
                ->with(['ente'])
                ->orderByPivot('registered_at', 'desc');
        } else {
            $query = Competition::query()
                ->with(['ente'])
                ->where('is_public', true)
                ->whereIn('status', [CompetitionStatus::PUBLISHED, CompetitionStatus::ACTIVE]);
        }

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.user.competitions.index', [
            'competitions' => $query->paginate(10),
            'user' => $user,
        ]);
    }
}
