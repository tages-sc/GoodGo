<?php

namespace App\Livewire\User\Competitions;

use App\Enums\TransportMode;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Competition $competition;

    public function mount(Competition $competition): void
    {
        $this->competition = $competition->load(['ente']);

        // Solo gare pubbliche o quelle a cui l'utente e iscritto
        if (!$this->competition->is_public && !$this->competition->hasUser(Auth::user())) {
            abort(404);
        }
    }

    public function subscribe(): void
    {
        $user = Auth::user();

        if ($this->competition->hasUser($user)) {
            session()->flash('error', 'Sei già iscritto a questa gara.');
            return;
        }

        if (!$this->competition->isRegistrationOpen()) {
            session()->flash('error', 'Le iscrizioni a questa gara sono chiuse.');
            return;
        }

        $ageEligible = $this->competition->isUserAgeEligible($user);
        if ($ageEligible === false) {
            $birthDate = $user->profile?->birth_date;
            if (!$birthDate) {
                session()->flash('error', 'Per iscriverti a questa gara devi compilare la data di nascita nel tuo profilo.');
            } else {
                session()->flash('error', 'Non puoi iscriverti a questa gara: la tua età non rientra nelle fasce ammesse (' . implode(', ', $this->competition->age_range) . ').');
            }
            return;
        }

        $status = $this->competition->moderated_subscription ? 'pending' : 'approved';

        $this->competition->users()->attach($user->id, [
            'status' => $status,
            'registered_at' => now(),
            'approved_at' => $status === 'approved' ? now() : null,
        ]);

        if ($status === 'approved') {
            $this->competition->updateStatistics();
        }

        $this->competition->refresh();

        $message = $status === 'pending'
            ? 'Richiesta di iscrizione inviata. In attesa di approvazione.'
            : 'Iscrizione completata con successo!';
        session()->flash('message', $message);
    }

    public function unsubscribe(): void
    {
        $user = Auth::user();

        if (!$this->competition->hasUser($user)) {
            session()->flash('error', 'Non sei iscritto a questa gara.');
            return;
        }

        $this->competition->users()->detach($user->id);
        $this->competition->updateStatistics();
        $this->competition->refresh();

        session()->flash('message', 'Disiscrizione completata.');
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

    public function render()
    {
        $user = Auth::user();
        $isEnrolled = $this->competition->hasUser($user);
        $userPivot = null;

        if ($isEnrolled) {
            $userPivot = $this->competition->users()
                ->where('user_id', $user->id)
                ->first()?->pivot;
        }

        return view('livewire.user.competitions.show', [
            'leaderboard' => $this->getLeaderboard(),
            'isEnrolled' => $isEnrolled,
            'userPivot' => $userPivot,
            'canSubscribe' => $this->competition->isRegistrationOpen() && !$isEnrolled,
            'transportModes' => TransportMode::cases(),
        ]);
    }
}
