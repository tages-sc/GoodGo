<?php

namespace App\Livewire\Admin\Competitions;

use App\Enums\TransportMode;
use App\Models\Competition;
use App\Models\Track;
use Livewire\Component;

class Show extends Component
{
    public Competition $competition;

    public function mount(Competition $competition): void
    {
        $this->competition = $competition->load(['ente', 'organizer']);
    }

    public function getLeaderboard()
    {
        return $this->competition->approvedUsers()
            ->orderByPivot('total_credits', 'desc')
            ->orderByPivot('total_distance_km', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Offusca il nome: "Mario Rossi" -> "Ma*** Ro***"
     */
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

    /**
     * Offusca l'email: "mario.rossi@gmail.com" -> "ma***@***.com"
     */
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

    /**
     * Restituisce la label di una modalita di trasporto dal valore stringa
     */
    public function getTransportModeLabel(string $value): string
    {
        $mode = TransportMode::tryFrom($value);

        return $mode ? $mode->label() : $value;
    }

    public function render()
    {
        return view('livewire.admin.competitions.show', [
            'leaderboard' => $this->getLeaderboard(),
            'tracksCount' => Track::where('competition_id', $this->competition->id)->count(),
            'validTracksCount' => Track::where('competition_id', $this->competition->id)->where('status', 'valid')->count(),
        ]);
    }
}
