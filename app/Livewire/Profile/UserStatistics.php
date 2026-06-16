<?php

namespace App\Livewire\Profile;

use App\Enums\TransportMode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserStatistics extends Component
{
    public function render()
    {
        $userId = Auth::id();

        // Emissioni risparmiate aggregate da tracce valide
        $emissions = DB::table('tracks')
            ->where('user_id', $userId)
            ->where('status', 'valid')
            ->selectRaw('
                COALESCE(SUM(co2_saved_grams), 0) as total_co2_grams,
                COALESCE(SUM(so2_saved_mg), 0) as total_so2_mg,
                COALESCE(SUM(nox_saved_grams), 0) as total_nox_grams,
                COALESCE(SUM(co_saved_grams), 0) as total_co_grams,
                COALESCE(SUM(pm10_saved_grams), 0) as total_pm10_grams,
                COALESCE(SUM(calories_burned), 0) as total_calories
            ')
            ->first();

        // Distanze per modalita di trasporto dai segmenti delle tracce valide
        $distancesByMode = DB::table('track_segments')
            ->join('tracks', 'tracks.id', '=', 'track_segments.track_id')
            ->where('tracks.user_id', $userId)
            ->where('tracks.status', 'valid')
            ->where('track_segments.status', 'valid')
            ->groupBy('track_segments.transport_mode')
            ->selectRaw('track_segments.transport_mode, SUM(track_segments.distance_meters) as total_meters')
            ->pluck('total_meters', 'transport_mode');

        // Prepara dati per modalita con labels
        $modeDistances = [];
        foreach (TransportMode::cases() as $mode) {
            $meters = $distancesByMode[$mode->value] ?? 0;
            if ($meters > 0) {
                $modeDistances[] = [
                    'label' => $mode->label(),
                    'km' => round($meters / 1000, 2),
                ];
            }
        }

        return view('livewire.profile.user-statistics', [
            'emissions' => $emissions,
            'modeDistances' => $modeDistances,
            'totalDistanceKm' => round($distancesByMode->sum() / 1000, 2),
        ]);
    }
}
