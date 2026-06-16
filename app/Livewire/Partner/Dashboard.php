<?php

namespace App\Livewire\Partner;

use App\Enums\MovementStatus;
use App\Models\Competition;
use App\Models\Movement;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $partnerId = auth()->id();

        // Statistiche gare
        $competitions = Competition::whereHas('partners', fn($q) => $q->where('user_id', $partnerId))
            ->withCount(['approvedUsers'])
            ->with(['ente'])
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();

        $competitionsStats = [
            'total' => Competition::whereHas('partners', fn($q) => $q->where('user_id', $partnerId))->count(),
            'active' => Competition::whereHas('partners', fn($q) => $q->where('user_id', $partnerId))->active()->count(),
        ];

        // Statistiche movimenti
        $movementsStats = [
            'pending' => Movement::where('partner_id', $partnerId)->where('status', MovementStatus::PENDING)->count(),
            'approved' => Movement::where('partner_id', $partnerId)->where('status', MovementStatus::APPROVED)->count(),
            'rejected' => Movement::where('partner_id', $partnerId)->where('status', MovementStatus::REJECTED)->count(),
            'total_credits' => Movement::where('partner_id', $partnerId)->where('status', MovementStatus::APPROVED)->sum('credits_amount'),
            'total_euro' => Movement::where('partner_id', $partnerId)->where('status', MovementStatus::APPROVED)->sum('euro_amount'),
        ];

        // Ultimi movimenti
        $recentMovements = Movement::where('partner_id', $partnerId)
            ->with(['user', 'competition'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.partner.dashboard', [
            'competitions' => $competitions,
            'competitionsStats' => $competitionsStats,
            'movementsStats' => $movementsStats,
            'recentMovements' => $recentMovements,
        ]);
    }
}
