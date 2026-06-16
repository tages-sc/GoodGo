<?php

namespace App\Livewire\User;

use App\Enums\MovementStatus;
use App\Enums\TransportMode;
use App\Models\Badge;
use App\Models\TrackSegment;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Statistiche gare
        $competitionsStats = [
            'subscribed' => $user->competitions()->count(),
            'active' => $user->approvedCompetitions()
                ->whereIn('competitions.status', ['active', 'published'])
                ->count(),
        ];

        // Crediti
        $creditsStats = [
            'balance' => (float) $user->credits,
            'total_earned' => (float) $user->creditLogs()
                ->whereIn('type', ['track_validation', 'manual_add', 'movement_refund', 'movement_reward', 'bonus'])
                ->sum('amount'),
        ];

        // Movimenti/Spese
        $movementsStats = [
            'total' => $user->movements()->count(),
            'approved' => $user->movements()->where('status', MovementStatus::APPROVED)->count(),
            'total_spent' => (float) $user->movements()
                ->where('status', MovementStatus::APPROVED)
                ->where('type', 'expense')
                ->sum('credits_amount'),
        ];

        // Km per modalita di trasporto
        $sustainableModes = [TransportMode::BIKE, TransportMode::WALK, TransportMode::TRAIN, TransportMode::BUS];
        $kmByMode = [];
        foreach ($sustainableModes as $mode) {
            $totalMeters = TrackSegment::whereHas('track', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->valid();
                })
                ->where('transport_mode', $mode)
                ->where('status', 'valid')
                ->sum('distance_meters');

            if ($totalMeters > 0) {
                $kmByMode[] = [
                    'mode' => $mode,
                    'km' => round($totalMeters / 1000, 1),
                ];
            }
        }

        // Badge vinti
        $earnedBadges = $user->badges()
            ->orderByDesc('user_badges.earned_at')
            ->limit(6)
            ->get();

        $badgesStats = [
            'earned' => $user->badges()->count(),
            'total' => Badge::active()->count(),
        ];

        return view('livewire.user.dashboard', [
            'competitionsStats' => $competitionsStats,
            'creditsStats' => $creditsStats,
            'movementsStats' => $movementsStats,
            'kmByMode' => $kmByMode,
            'earnedBadges' => $earnedBadges,
            'badgesStats' => $badgesStats,
        ]);
    }
}
