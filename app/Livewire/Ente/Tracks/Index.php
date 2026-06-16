<?php

namespace App\Livewire\Ente\Tracks;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Enums\UserType;
use App\Models\Competition;
use App\Models\Track;
use App\Services\TrackValidationManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public Competition $competition;

    // Modal states
    public bool $showValidateModal = false;
    public ?int $validatingId = null;

    // Validation form
    public string $validationAction = 'valid';
    public string $rejectionReason = '';

    // Filters
    public string $search = '';
    public string $filterStatus = '';
    public string $filterTransportMode = '';

    public function mount(Competition $competition): void
    {
        $this->competition = $competition;
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();

        if ($user->type === UserType::ENTE) {
            if ($this->competition->ente_id !== $user->id) {
                abort(403, 'Non hai accesso alle tracce di questa gara.');
            }
        } elseif ($user->type === UserType::ORGANIZER) {
            if ($this->competition->organizer_id !== $user->id) {
                abort(403, 'Non hai accesso alle tracce di questa gara.');
            }
        } else {
            abort(403, 'Accesso non autorizzato.');
        }
    }

    public function openValidateModal(int $id): void
    {
        $this->validatingId = $id;
        $this->validationAction = 'valid';
        $this->rejectionReason = '';
        $this->showValidateModal = true;
    }

    public function validate_track(TrackValidationManager $validationManager): void
    {
        if (!$this->validatingId) {
            return;
        }

        $track = Track::where('competition_id', $this->competition->id)
            ->findOrFail($this->validatingId);

        if ($this->validationAction === 'valid') {
            $creditsAdded = $validationManager->validate($track, auth()->id());
            session()->flash('message', 'Traccia validata con successo.' . ($creditsAdded > 0 ? " Assegnati {$creditsAdded} crediti." : ''));
        } else {
            if (empty($this->rejectionReason)) {
                $this->addError('rejectionReason', 'La motivazione è obbligatoria per invalidare una traccia.');
                return;
            }

            $creditsRemoved = $validationManager->invalidate($track, $this->rejectionReason, auth()->id());
            session()->flash('message', 'Traccia invalidata.' . ($creditsRemoved > 0 ? " Rimossi {$creditsRemoved} crediti." : ''));
        }

        $this->showValidateModal = false;
        $this->validatingId = null;
    }

    public function quickValidate(int $id, TrackValidationManager $validationManager): void
    {
        $track = Track::where('competition_id', $this->competition->id)
            ->findOrFail($id);

        $creditsAdded = $validationManager->validate($track, auth()->id());
        session()->flash('message', 'Traccia validata con successo.' . ($creditsAdded > 0 ? " Assegnati {$creditsAdded} crediti." : ''));
    }

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (TrackStatus::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public function getTransportModes(): array
    {
        $modes = [];
        foreach (TransportMode::cases() as $mode) {
            $modes[$mode->value] = $mode->label();
        }
        return $modes;
    }

    /**
     * Determina il nome della rotta per il dettaglio traccia in base al ruolo
     */
    public function getShowRouteName(): string
    {
        $user = Auth::user();

        if ($user->type === UserType::ENTE) {
            return 'ente.competitions.tracks.show';
        }

        return 'organizer.competitions.tracks.show';
    }

    /**
     * Determina il nome della rotta per tornare alla lista gare
     */
    public function getBackRouteName(): string
    {
        $user = Auth::user();

        if ($user->type === UserType::ENTE) {
            return 'ente.competitions';
        }

        return 'organizer.competitions';
    }

    public function render()
    {
        $query = Track::query()
            ->where('competition_id', $this->competition->id)
            ->with(['user', 'competition', 'validator', 'segments'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('session_id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterTransportMode) {
            $query->where('primary_transport_mode', $this->filterTransportMode);
        }

        return view('livewire.ente.tracks.index', [
            'tracks' => $query->paginate(15),
            'statuses' => $this->getStatuses(),
            'transportModes' => $this->getTransportModes(),
            'showRouteName' => $this->getShowRouteName(),
            'backRouteName' => $this->getBackRouteName(),
        ]);
    }
}
