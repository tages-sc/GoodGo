<?php

namespace App\Livewire\Ente\Competitions;

use App\Enums\TransportMode;
use App\Enums\UserType;
use App\Models\Competition;
use App\Models\Track;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Competition $competition;

    public function mount(Competition $competition): void
    {
        $this->competition = $competition->load(['ente', 'organizer']);
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

    public function getLeaderboard()
    {
        return $this->competition->approvedUsers()
            ->orderByPivot('total_credits', 'desc')
            ->orderByPivot('total_distance_km', 'desc')
            ->limit(50)
            ->get();
    }

    public function obfuscateName(string $name): string
    {
        $parts = explode(' ', $name);

        return implode(' ', array_map(function ($part) {
            if (strlen($part) <= 2) {
                return $part;
            }

            return substr($part, 0, 2) . str_repeat('*', strlen($part) - 2);
        }, $parts));
    }

    public function obfuscateEmail(string $email): string
    {
        $parts = explode('@', $email);
        $local = strlen($parts[0]) > 2
            ? substr($parts[0], 0, 2) . '***'
            : $parts[0] . '***';
        $domainParts = explode('.', $parts[1]);
        $ext = end($domainParts);

        return $local . '@***.' . $ext;
    }

    public function getRoutePrefix(): string
    {
        return Auth::user()->type === UserType::ENTE ? 'ente' : 'organizer';
    }

    public function render()
    {
        $prefix = $this->getRoutePrefix();

        return view('livewire.ente.competitions.show', [
            'leaderboard' => $this->getLeaderboard(),
            'tracksCount' => Track::where('competition_id', $this->competition->id)->count(),
            'validTracksCount' => Track::where('competition_id', $this->competition->id)->where('status', 'valid')->count(),
            'participantsRoute' => $prefix . '.competitions.participants',
            'tracksRoute' => $prefix . '.competitions.tracks',
            'backRoute' => $prefix . '.competitions',
            'transportModes' => TransportMode::cases(),
        ]);
    }
}
