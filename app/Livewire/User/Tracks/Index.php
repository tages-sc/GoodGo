<?php

namespace App\Livewire\User\Tracks;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $filterStatus = '';
    public string $filterTransportMode = '';
    public string $filterCompetition = '';

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

    public function render()
    {
        $user = Auth::user();

        $query = Track::query()
            ->where('user_id', $user->id)
            ->with(['competition', 'segments'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('session_id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('competition', function ($cq) {
                        $cq->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterTransportMode) {
            $query->where('primary_transport_mode', $this->filterTransportMode);
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        // Gare a cui l'utente è iscritto (per il filtro)
        $competitions = $user->competitions()->orderBy('name')->get();

        return view('livewire.user.tracks.index', [
            'tracks' => $query->paginate(15),
            'statuses' => $this->getStatuses(),
            'transportModes' => $this->getTransportModes(),
            'competitions' => $competitions,
        ]);
    }
}
