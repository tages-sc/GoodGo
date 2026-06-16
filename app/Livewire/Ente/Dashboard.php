<?php

namespace App\Livewire\Ente;

use App\Enums\CompetitionStatus;
use App\Enums\MovementStatus;
use App\Models\Competition;
use App\Models\Movement;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Determina le gare in base al ruolo
        if ($user->isOrganizer()) {
            $competitionsQuery = $user->assignedCompetitions();
        } else {
            $competitionsQuery = $user->organizedCompetitions();
        }

        // Statistiche gare
        $competitions = $competitionsQuery->clone()
            ->withCount(['approvedUsers', 'approvedPartners'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $competitionsStats = [
            'total' => $competitionsQuery->clone()->count(),
            'active' => $competitionsQuery->clone()->where('status', CompetitionStatus::ACTIVE)->count(),
            'total_participants' => $competitionsQuery->clone()
                ->withCount('approvedUsers')
                ->get()
                ->sum('approved_users_count'),
            'total_partners' => $competitionsQuery->clone()
                ->withCount('approvedPartners')
                ->get()
                ->sum('approved_partners_count'),
        ];

        // Crediti utilizzati nelle gare
        $competitionIds = $competitionsQuery->clone()->pluck('id');
        $creditsStats = [
            'total_credits' => (float) Movement::whereIn('competition_id', $competitionIds)
                ->where('status', MovementStatus::APPROVED)
                ->sum('credits_amount'),
            'total_euro' => (float) Movement::whereIn('competition_id', $competitionIds)
                ->where('status', MovementStatus::APPROVED)
                ->sum('euro_amount'),
        ];

        return view('livewire.ente.dashboard', [
            'competitions' => $competitions,
            'competitionsStats' => $competitionsStats,
            'creditsStats' => $creditsStats,
            'isOrganizer' => $user->isOrganizer(),
        ]);
    }
}
